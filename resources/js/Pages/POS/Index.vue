<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DrugSearch from '@/Components/DrugSearch.vue';
import CartItem from '@/Components/CartItem.vue';
import PaymentModal from '@/Components/PaymentModal.vue';

const props = defineProps({
    recentSales: Array,
    todaySales: Number,
    todayTransactions: Number,
});

const cart = ref([]);
const discount = ref(0);
const showPaymentModal = ref(false);
const processing = ref(false);
const barcodeInput = ref('');

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => {
        return sum + (item.unit_price * item.quantity) - (item.discount || 0);
    }, 0);
});

const tax = computed(() => {
    return (subtotal.value - discount.value) * 0.075; // 7.5% tax
});

const total = computed(() => {
    return subtotal.value - discount.value + tax.value;
});

const addToCart = (drug) => {
    const existingItem = cart.value.find(item => item.drug_id === drug.id);

    if (existingItem) {
        // Check stock limit
        if (existingItem.quantity + 1 > existingItem.stock) {
            alert(`Only ${existingItem.stock} units available in stock`);
            return;
        }
        existingItem.quantity += 1;
    } else {
        cart.value.push({
            drug_id: drug.id,
            drug_name: drug.drug_name || drug.name,
            generic_name: drug.generic_name,
            batch_number: drug.batch_number,
            quantity: 1,
            unit_price: parseFloat(drug.selling_price),
            discount: 0,
            stock: drug.total_sachets_in_stock || drug.quantity,
            requires_prescription: drug.requires_prescription,
            expiry_date: drug.expiry_date,
            packaging: drug.packaging,
            drug_dose: drug.drug_dose,
            dose_unit: drug.dose_unit,
            drug_company: drug.drug_company,
        });
    }

    // Clear barcode input after adding
    barcodeInput.value = '';
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const updateQuantity = (index, quantity) => {
    if (quantity <= 0) {
        removeFromCart(index);
        return;
    }

    const item = cart.value[index];
    if (quantity > item.stock) {
        alert(`Only ${item.stock} units available in stock`);
        return;
    }

    item.quantity = quantity;
};

const updateItemDiscount = (index, discountValue) => {
    cart.value[index].discount = parseFloat(discountValue) || 0;
};

const clearCart = () => {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart.value = [];
        discount.value = 0;
    }
};

const holdSale = () => {
    if (cart.value.length === 0) {
        alert('Cart is empty');
        return;
    }

    axios.post('/pos/hold', {
        items: cart.value,
        subtotal: subtotal.value,
        tax: tax.value,
        discount: discount.value,
        total: total.value,
    })
    .then(response => {
        alert('Sale held successfully');
        clearCart();
    })
    .catch(error => {
        alert('Failed to hold sale: ' + (error.response?.data?.message || 'Unknown error'));
    });
};

const openPaymentModal = () => {
    if (cart.value.length === 0) {
        alert('Cart is empty');
        return;
    }

    showPaymentModal.value = true;
};

const processSale = (paymentMethod, notes = '') => {
    processing.value = true;

    axios.post('/pos/sales', {
        items: cart.value,
        payment_method: paymentMethod,
        discount: discount.value,
        notes: notes,
    })
    .then(response => {
        alert(`Sale completed! Invoice: ${response.data.invoice_number}`);
        showPaymentModal.value = false;

        // Print receipt option
        if (confirm('Do you want to print the receipt?')) {
            window.open(`/pos/sales/${response.data.sale.id}/print`, '_blank');
        }

        clearCart();
    })
    .catch(error => {
        if (error.response?.data?.error === 'insufficient_stock') {
            alert(error.response.data.message);
        } else {
            alert('Failed to process sale: ' + (error.response?.data?.message || 'Please try again.'));
        }
    })
    .finally(() => {
        processing.value = false;
    });
};

// Barcode Scanner Support
const handleBarcodeInput = (event) => {
    // Barcode scanners typically send input fast and end with Enter
    if (event.key === 'Enter' && barcodeInput.value.trim()) {
        searchByBarcode(barcodeInput.value.trim());
        barcodeInput.value = '';
    }
};

const searchByBarcode = async (barcode) => {
    try {
        const response = await axios.get('/pos/drugs/search', {
            params: { query: barcode }
        });

        if (response.data.drugs && response.data.drugs.length > 0) {
            addToCart(response.data.drugs[0]);
        } else {
            alert('Product not found');
        }
    } catch (error) {
        console.error('Barcode search failed:', error);
        alert('Failed to find product');
    }
};

// Keyboard shortcuts
const handleKeyPress = (event) => {
    // F1 - Focus on search/barcode
    if (event.key === 'F1') {
        event.preventDefault();
        document.querySelector('input[type="text"]')?.focus();
    }
    // F2 - Clear cart
    if (event.key === 'F2') {
        event.preventDefault();
        clearCart();
    }
    // F3 - Hold sale
    if (event.key === 'F3') {
        event.preventDefault();
        holdSale();
    }
    // F4 - Complete sale
    if (event.key === 'F4') {
        event.preventDefault();
        openPaymentModal();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyPress);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyPress);
});
</script>

<template>
    <AppLayout title="Point of Sale">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Point of Sale</h1>
                            <p class="text-sm text-gray-500 mt-1">
                                Today's Sales: ₦{{ todaySales?.toLocaleString() || '0.00' }} |
                                Transactions: {{ todayTransactions || 0 }}
                            </p>
                        </div>
                        <div class="flex space-x-3">
                        <button
                            @click="router.visit(route('pos.sales'))"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                        >
                            View Transactions
                        </button>

                        <button
                            @click="holdSale"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition"
                            title="Hold Sale (F3)"
                        >
                            <span class="hidden sm:inline">Hold Sale</span>
                            <span class="sm:hidden">Hold</span>
                        </button>
                         <button
                            @click="router.visit(route('pos.held'))"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition"
                            title="Hold Sale (F3)"
                        >
                            <span class="hidden sm:inline">Held Sales</span>
                            <span class="sm:hidden">Held Sales</span>
                        </button>

                        <button
                            @click="clearCart"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
                            title="Clear Cart (F2)"
                        >
                            Clear
                        </button>
                    </div>

                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Product Search & Cart -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Barcode Scanner Input -->
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-blue-900 mb-1">
                                        Scan Barcode (F1)
                                    </label>
                                    <input
                                        v-model="barcodeInput"
                                        @keydown="handleBarcodeInput"
                                        type="text"
                                        placeholder="Scan or type barcode and press Enter..."
                                        class="w-full px-4 py-2 border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        autofocus
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Drug Search -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold mb-4">Search Products</h2>
                            <DrugSearch @add-to-cart="addToCart" />
                        </div>

                        <!-- Cart Items -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold">
                                    Cart ({{ cart.length }} items)
                                </h2>
                                <span v-if="cart.length > 0" class="text-sm text-gray-500">
                                    {{ cart.reduce((sum, item) => sum + item.quantity, 0) }} units total
                                </span>
                            </div>

                            <div v-if="cart.length === 0" class="text-center py-12 text-gray-400">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="mt-4">Cart is empty</p>
                                <p class="text-sm mt-2">Scan a barcode or search for products to add</p>
                            </div>

                            <div v-else class="space-y-3">
                                <CartItem
                                    v-for="(item, index) in cart"
                                    :key="index"
                                    :item="item"
                                    @remove="removeFromCart(index)"
                                    @update-quantity="updateQuantity(index, $event)"
                                    @update-discount="updateItemDiscount(index, $event)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Payment Summary -->
                    <div class="space-y-6">
                        <!-- Totals -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold mb-4">Summary</h2>

                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium">₦{{ subtotal.toFixed(2) }}</span>
                                </div>

                                <div class="flex justify-between text-sm items-center">
                                    <label class="text-gray-600">Discount</label>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500">₦</span>
                                        <input
                                            v-model.number="discount"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :max="subtotal"
                                            class="w-24 px-2 py-1 text-right border rounded focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax (7.5%)</span>
                                    <span class="font-medium">₦{{ tax.toFixed(2) }}</span>
                                </div>

                                <div class="border-t pt-3 flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span class="text-blue-600">₦{{ total.toFixed(2) }}</span>
                                </div>
                            </div>

                            <button
                                @click="openPaymentModal"
                                :disabled="cart.length === 0 || processing"
                                class="w-full mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold text-lg transition shadow-lg hover:shadow-xl"
                                title="Complete Sale (F4)"
                            >
                                {{ processing ? 'Processing...' : 'Complete Sale' }}
                            </button>
                        </div>

                        <!-- Quick Tips -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-900 mb-2">Keyboard Shortcuts</h3>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li><kbd class="px-2 py-1 bg-white rounded shadow-sm">F1</kbd> Focus Search</li>
                                <li><kbd class="px-2 py-1 bg-white rounded shadow-sm">F2</kbd> Clear Cart</li>
                                <li><kbd class="px-2 py-1 bg-white rounded shadow-sm">F3</kbd> Hold Sale</li>
                                <li><kbd class="px-2 py-1 bg-white rounded shadow-sm">F4</kbd> Complete Sale</li>
                            </ul>
                        </div>

                        <!-- Recent Sales -->
                        <div v-if="recentSales && recentSales.length > 0" class="bg-white rounded-lg shadow p-6">
                            <h3 class="font-semibold mb-3">Recent Transactions</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="sale in recentSales.slice(0, 5)"
                                    :key="sale.id"
                                    class="flex justify-between text-sm p-2 hover:bg-gray-50 rounded cursor-pointer"
                                    @click="router.visit(`/pos/sales/${sale.id}`)"
                                >
                                    <span class="text-gray-600">{{ sale.invoice_number }}</span>
                                    <span class="font-medium">₦{{ parseFloat(sale.total).toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <PaymentModal
            v-if="showPaymentModal"
            :total="total"
            :processing="processing"
            @close="showPaymentModal = false"
            @process="processSale"
        />
    </AppLayout>
</template>

<style scoped>
kbd {
    font-family: monospace;
    font-size: 0.875rem;
}
</style>
