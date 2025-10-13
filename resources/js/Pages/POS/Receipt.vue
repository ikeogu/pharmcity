<script setup>
import { onMounted } from 'vue';

const props = defineProps({
    sale: {
        type: Object,
        required: true
    }
});

onMounted(() => {
    // Auto-print when component mounts
    setTimeout(() => {
        window.print();
    }, 500);
});

const handlePrint = () => {
  try {
    window.print()
  } catch (error) {
    console.error('Print failed:', error)
    alert('Unable to print from this window.')
  }
}

const handleClose = () => {
  try {
    if (window.opener) {
      window.close()
    } else {
      history.back()
    }
  } catch (error) {
    console.error('Close failed:', error)
    alert('Unable to close this window. Please close it manually.')
  }
}


</script>

<template>
    <div class="receipt-container">
        <div class="receipt">
            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">PharmCity</h1>
                <p class="text-sm">Your Trusted Pharmacy</p>
                <p class="text-xs mt-1">123 Health Street, Medical District</p>
                <p class="text-xs">Tel: +234 123 456 7890</p>
                <p class="text-xs">Email: info@pharmcity.com</p>
            </div>

            <div class="border-t-2 border-b-2 border-dashed py-3 mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="font-semibold">Invoice #:</p>
                        <p>{{ sale.invoice_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">Date:</p>
                        <p>{{ new Date(sale.created_at).toLocaleString() }}</p>
                    </div>
                </div>

                <div v-if="sale.customer" class="mt-3">
                    <p class="font-semibold text-sm">Customer:</p>
                    <p class="text-sm">{{ sale.customer.name }}</p>
                    <p class="text-xs">{{ sale.customer.phone }}</p>
                </div>

                <div v-if="sale.prescription" class="mt-3">
                    <p class="font-semibold text-sm">Prescription:</p>
                    <p class="text-xs">{{ sale.prescription.prescription_number }}</p>
                </div>

                <div class="mt-3">
                    <p class="font-semibold text-sm">Cashier:</p>
                    <p class="text-xs">{{ sale.user.first_name }}</p>
                </div>
            </div>

            <!-- Items -->
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in sale.items"
                        :key="item.id"
                        class="border-b"
                    >
                        <td class="py-2">
                            <div>
                                <p class="font-medium">{{ item.drug_name }}</p>
                                <p v-if="item.batch_id" class="text-xs text-gray-600">
                                    Batch: {{ item.batch?.batch_number }}
                                </p>
                            </div>
                        </td>
                        <td class="text-center">{{ item.quantity }}</td>
                        <td class="text-right">₦{{ Number(item.unit_price).toFixed(2) }}</td>
                        <td class="text-right font-medium">₦{{ Number(item.subtotal).toFixed(2) }}</td>

                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="border-t-2 pt-3 space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Subtotal:</span>
                    <span>₦{{ Number(sale.subtotal).toFixed(2) }}</span>
                </div>

                <div v-if="sale.discount > 0" class="flex justify-between text-sm">
                    <span>Discount:</span>
                    <span class="text-red-600">-₦{{ Number(sale.discount).toFixed(2) }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span>Tax (7.5%):</span>
                    <span>₦{{ Number(sale.tax).toFixed(2) }}</span>
                </div>

                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>TOTAL:</span>
                    <span>₦{{ Number(sale.total).toFixed(2) }}</span>
                </div>

                <div class="flex justify-between text-sm mt-3">
                    <span class="capitalize">Payment Method:</span>
                    <span class="font-medium capitalize">{{ sale.payment_method }}</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t-2 border-dashed mt-6 pt-4 text-center text-xs">
                <p class="font-semibold mb-2">Thank you for your patronage!</p>
                <p>Please keep this receipt for your records</p>
                <p class="mt-2">For inquiries, contact us at support@pharmcity.com</p>

                <div v-if="sale.notes" class="mt-4 text-left bg-gray-100 p-2 rounded">
                    <p class="font-semibold">Notes:</p>
                    <p>{{ sale.notes }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-xs text-gray-500">Powered by PharmCity POS</p>
                </div>
            </div>
        </div>

        <div class="no-print mt-4 text-center">
            <button
                @click="handlePrint"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium mr-3"
            >
                Print Receipt
            </button>
            <button
                @click="handleClose"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium"
            >
                Close
            </button>
        </div>
    </div>
</template>

<style scoped>
.receipt-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
}

.receipt {
    background: white;
    padding: 20px;
    font-family: 'Courier New', monospace;
}

@media print {
    body {
        margin: 0;
        padding: 0;
    }

    .receipt-container {
        max-width: 80mm;
        padding: 0;
    }

    .receipt {
        padding: 10px;
        font-size: 12px;
    }

    .no-print {
        display: none;
    }

    h1 {
        font-size: 18px;
    }
}
</style>
