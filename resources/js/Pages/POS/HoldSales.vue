<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  sales: Array,
})
</script>

<template>
  <AppLayout title="Held Sales">
    <div class="max-w-5xl mx-auto py-8 px-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Held Sales</h1>
        <Link
          href="/pos/sales"
          class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
        >
          Back to Sales
        </Link>
      </div>

      <table class="w-full border-collapse">
        <thead class="bg-gray-100">
          <tr class="text-left">
            <th class="p-3">Invoice</th>
            <th class="p-3">Date</th>
            <th class="p-3">Total</th>
            <th class="p-3">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="sale in sales"
            :key="sale.id"
            class="border-t hover:bg-gray-50"
          >
            <td class="p-3">{{ sale.invoice_number }}</td>
            <td class="p-3">{{ new Date(sale.created_at).toLocaleString() }}</td>
            <td class="p-3">₦{{ parseFloat(sale.total).toLocaleString() }}</td>
            <td class="p-3">
              <Link
                :href="route('pos.resume', sale.id)"
                class="text-blue-600 hover:underline"
              >
                Resume Sale
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="sales.length === 0" class="text-center text-gray-500 mt-10">
        No held sales yet.
      </div>
    </div>
  </AppLayout>
</template>
