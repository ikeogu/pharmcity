<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const start_date = ref(props.filters.start_date || '');
const end_date = ref(props.filters.end_date || '');

const applyFilters = () => {
    router.get(route('pos.sales'), { start_date: start_date.value, end_date: end_date.value }, { preserveState: true });
};

const resetFilters = () => {
    start_date.value = '';
    end_date.value = '';
    router.get(route('pos.sales'), {}, { preserveState: true });
};
</script>

<template>
    <AppLayout title="All Sales">
        <div class="max-w-6xl mx-auto py-10 px-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">All Transactions</h1>
                <Link href="/pos" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Back to POS
                </Link>
            </div>

            <!-- Filter Section -->
            <div class="bg-white shadow rounded-lg p-4 mb-6 flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm text-gray-600">Start Date</label>
                    <input
                        id="start_date"
                        type="date"
                        v-model="start_date"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div class="flex-1">
                    <label for="end_date" class="block text-sm text-gray-600">End Date</label>
                    <input
                        id="end_date"
                        type="date"
                        v-model="end_date"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div class="flex space-x-2">
                    <button
                        @click="applyFilters"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                    >
                        Filter
                    </button>
                    <button
                        @click="resetFilters"
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr class="text-left text-gray-700">
                            <th class="p-3">Invoice #</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Payment Method</th>
                            <th class="p-3">Date</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="sale in sales.data"
                            :key="sale.id"
                            class="border-t hover:bg-gray-50 transition"
                        >
                            <td class="p-3">{{ sale.invoice_number }}</td>
                            <td class="p-3">₦{{ parseFloat(sale.total).toLocaleString() }}</td>
                            <td class="p-3 capitalize">{{ sale.payment_method }}</td>
                            <td class="p-3">{{ new Date(sale.created_at).toLocaleString() }}</td>
                            <td class="p-3 text-right space-x-2">
                                <Link
                                    :href="route('pos.show', sale.id)"
                                    class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm"
                                >
                                    View
                                </Link>
                                <a
                                    :href="route('pos.print', sale.id)"
                                    target="_blank"
                                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                                >
                                    Print
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-end space-x-2">
                <Link
                    v-if="sales.prev_page_url"
                    :href="sales.prev_page_url"
                    class="px-3 py-1 border rounded text-sm text-gray-700 hover:bg-gray-100"
                >
                    Prev
                </Link>
                <Link
                    v-if="sales.next_page_url"
                    :href="sales.next_page_url"
                    class="px-3 py-1 border rounded text-sm text-gray-700 hover:bg-gray-100"
                >
                    Next
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
