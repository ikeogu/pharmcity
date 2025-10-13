<script setup>
import { computed } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['remove', 'update-quantity', 'update-discount']);

const itemTotal = computed(() => {
    return (props.item.unit_price * props.item.quantity) - (props.item.discount || 0);
});

const incrementQuantity = () => {
    if (props.item.quantity >= props.item.stock) {
        alert(`Only ${props.item.stock} units available in stock`);
        return;
    }
    emit('update-quantity', props.item.quantity + 1);
};

const decrementQuantity = () => {
    emit('update-quantity', props.item.quantity - 1);
};

const updateQuantity = (event) => {
    const value = parseInt(event.target.value) || 0;
    emit('update-quantity', value);
};

const updateDiscount = (event) => {
    const value = parseFloat(event.target.value) || 0;
    emit('update-discount', value);
};

const getExpiryWarning = () => {
    if (!props.item.expiry_date) return null;

    const expiryDate = new Date(props.item.expiry_date);
    const today = new Date();
    const diffTime = expiryDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return { type: 'expired', message: 'Expired!' };
    if (diffDays <= 30) return { type: 'critical', message: `Expires in ${diffDays} days` };
    if (diffDays <= 90) return { type: 'warning', message: `Expires in ${diffDays} days` };
    return null;
};

const expiryWarning = computed(() => getExpiryWarning());
</script>

<template>
    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 transition">
        <div class="flex-1">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">{{ item.drug_name }}</h4>
                    <p v-if="item.generic_name" class="text-xs text-gray-500 mt-0.5">
                        {{ item.generic_name }}
                    </p>
                    <div class="flex items-center flex-wrap gap-2 mt-1 text-xs text-gray-500">
                        <!-- Batch Number -->
                        <span v-if="item.batch_number" class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Batch: {{ item.batch_number }}
                        </span>

                        <!-- Packaging -->
                        <span v-if="item.packaging" class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            {{ item.packaging }}
                        </span>

                        <!-- Expiry Date -->
                        <span v-if="item.expiry_date"
                              :class="{
                                  'bg-red-100 text-red-800': expiryWarning?.type === 'expired' || expiryWarning?.type === 'critical',
                                  'bg-orange-100 text-orange-800': expiryWarning?.type === 'warning',
                                  'bg-gray-100 text-gray-600': !expiryWarning
                              }"
                              class="inline-flex items-center px-2 py-0.5 rounded">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ expiryWarning?.message || `Exp: ${new Date(item.expiry_date).toLocaleDateString()}` }}
                        </span>

                        <!-- Drug Dose -->
                        <span v-if="item.drug_dose || item.dose_unit" class="inline-flex items-center px-2 py-0.5 rounded bg-purple-100 text-purple-800">
                            {{ item.drug_dose }}{{ item.dose_unit }}
                        </span>

                        <!-- Prescription Required -->
                        <span v-if="item.requires_prescription" class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            ℞ Rx Required
                        </span>
                    </div>
                </div>

                <button
                    @click="$emit('remove')"
                    class="ml-2 p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition"
                    title="Remove item"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                <!-- Quantity Controls -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                        <button
                            @click="decrementQuantity"
                            class="px-3 py-2 hover:bg-gray-100 transition text-gray-700 font-semibold"
                            :disabled="item.quantity <= 1"
                        >
                            -
                        </button>
                        <input
                            :value="item.quantity"
                            @input="updateQuantity"
                            type="number"
                            min="1"
                            :max="item.stock"
                            class="w-full text-center border-x border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <button
                            @click="incrementQuantity"
                            class="px-3 py-2 hover:bg-gray-100 transition text-gray-700 font-semibold"
                            :disabled="item.quantity >= item.stock"
                        >
                            +
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Available: <span class="font-medium">{{ item.stock }}</span>
                    </p>
                </div>

                <!-- Unit Price -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit Price</label>
                    <div class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium">
                        ₦{{ item.unit_price.toFixed(2) }}
                    </div>
                </div>

                <!-- Discount -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Discount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500 text-sm">₦</span>
                        <input
                            :value="item.discount || 0"
                            @input="updateDiscount"
                            type="number"
                            min="0"
                            step="0.01"
                            :max="item.unit_price * item.quantity"
                            class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Subtotal -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtotal</label>
                    <div class="px-3 py-2 bg-blue-50 border-2 border-blue-200 rounded-lg text-sm font-bold text-blue-700">
                        ₦{{ itemTotal.toFixed(2) }}
                    </div>
                </div>
            </div>

            <!-- Low Stock Warning -->
            <div v-if="item.stock <= 10 && item.stock > 0" class="mt-3 flex items-center text-xs text-orange-600">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Low stock warning: Only {{ item.stock }} units left
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Remove spinner arrows from number inputs */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

/* Disable state styles */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
