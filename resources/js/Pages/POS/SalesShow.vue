<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    sale: Object,
});

const sale = props.sale;

const printInvoice = () => {
    window.open(route('pos.print', sale.id), '_blank');
};

const formatMoney = (value) => {
    const num = parseFloat(value)
    if (isNaN(num)) return '0.00'
    return num.toFixed(2)
}

</script>

<template>
    <AppLayout title="Sale Details">
        <div class="max-w-4xl mx-auto py-10 px-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Invoice #{{ sale.invoice_number }}</h1>
                <Link
                    href="/pos/sales"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
                >
                    Back to Sales
                </Link>
            </div>

            <!-- Sale Summary -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h2 class="text-gray-600 text-sm">Invoice Number</h2>
                        <p class="text-lg font-semibold">{{ sale.invoice_number }}</p>
                    </div>

                    <div>
                        <h2 class="text-gray-600 text-sm">Payment Method</h2>
                        <p class="text-lg font-semibold capitalize">{{ sale.payment_method }}</p>
                    </div>

                    <div>
                        <h2 class="text-gray-600 text-sm">Date</h2>
                        <p class="text-lg font-semibold">
                            {{ new Date(sale.created_at).toLocaleString() }}
                        </p>
                    </div>

                    <div>
                        <h2 class="text-gray-600 text-sm">Total Amount</h2>
                        <p class="text-lg font-semibold text-green-700">
                            ₦{{ parseFloat(sale.total).toLocaleString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sale Items -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100 text-left text-gray-700">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">Drug Name</th>
                            <th class="p-3">Quantity</th>
                            <th class="p-3">Unit Price</th>
                            <th class="p-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, index) in sale.items"
                            :key="item.id"
                            class="border-t hover:bg-gray-50 transition"
                        >
                            <td class="p-3">{{ index + 1 }}</td>
                            <td class="p-3">{{ item.drug?.drug_name || 'N/A' }}</td>
                            <td class="p-3">{{ item.quantity }}</td>
                            <td class="p-3">₦{{ formatMoney(item.unit_price ?? item.price) }}</td>
                            <td class="p-3 text-right">
                                ₦{{ formatMoney((item.quantity || 0) * (item.unit_price ?? item.price))  }}
                            </td>
                        </tr>
                    </tbody>
                </table>


                 <div class="p-4 bg-gray-50 flex justify-between items-center border-t">

                    <span class="font-semibold text-gray-700">Tax:</span>
                    <span class="text-xl font-bold text-gray-700">
                        ₦{{ parseFloat(sale.tax).toLocaleString() }}
                    </span>
                </div>
                <!-- Total -->
                <div class="p-4 bg-gray-50 flex justify-between items-center border-t">

                    <span class="font-semibold text-gray-700">Grand Total:</span>
                    <span class="text-xl font-bold text-green-700">
                        ₦{{ parseFloat(sale.total).toLocaleString() }}
                    </span>
                </div>
            </div>

            <!-- Print Button -->
            <div class="mt-6 flex justify-end">
                <button
                    @click="printInvoice"
                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"
                >
                    Print Invoice
                </button>
            </div>
        </div>
    </AppLayout>
</template>
