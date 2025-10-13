<script setup>
import { ref, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  sale: Object,
})

const sale = ref(props.sale)
const items = ref(sale.value.items || [])

const total = computed(() =>
  items.value.reduce((sum, item) => sum + (item.quantity * item.price), 0)
)

const finalizeSale = () => {
  router.put(route('pos.finalize', sale.value.id), {
    items: items.value,
    total: total.value,
    status: 'completed',
    payment_method: sale.value.payment_method || 'cash',
  })
}
</script>

<template>
  <AppLayout title="Resume Held Sale">
    <div class="max-w-5xl mx-auto py-8 px-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
          Resume Held Sale #{{ sale.invoice_number }}
        </h1>
        <Link href="/pos/hold-sales" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
          Back
        </Link>
      </div>

      <table class="w-full border-collapse mb-4">
        <thead class="bg-gray-100">
          <tr class="text-left">
            <th class="p-3">Drug</th>
            <th class="p-3">Qty</th>
            <th class="p-3">Price</th>
            <th class="p-3 text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id" class="border-t">
            <td class="p-3">{{ item.drug?.drug_name || 'N/A' }}</td>
            <td class="p-3">{{ item.quantity }}</td>
            <td class="p-3">₦{{ Number(item.price).toFixed(2) }}</td>
            <td class="p-3 text-right">₦{{ (item.quantity * item.price).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-between items-center bg-gray-50 p-4 rounded border-t">
        <span class="font-semibold text-gray-700">Total:</span>
        <span class="text-xl font-bold text-green-700">₦{{ total.toFixed(2) }}</span>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button
          @click="finalizeSale"
          class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700"
        >
          Complete Sale
        </button>
      </div>
    </div>
  </AppLayout>
</template>
