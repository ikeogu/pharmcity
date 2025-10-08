<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

interface Drug {
  id: number
  drug_name?: string
  generic_name?: string
  drug_dose?: string
  dose_unit?: string
  drug_group_class?: string
  min_daily_dose?: number | null
  max_daily_dose?: number | null
  total_sachets_in_stock?: number
  price_per_sachet?: number
  batch_number?: string
  expiry_date?: string // ISO date string
  drug_route?: string
  is_expired?: boolean
  is_low_stock?: boolean
  // possible extra fields used by some views:
  time?: string
  patient?: string
  source?: string
  status?: string
  // for pending orders
  date_of_order?: string
  diagnosis?: string
  unit?: string
  ordered_by?: string
  consultant?: string
}

interface PaginatedDrugs {
  data: Drug[]
  current_page?: number
  last_page?: number
  per_page?: number
  total?: number
  meta?: { current_page?: number; last_page?: number; total?: number } // some apis nest pagination
}

interface Props {
  drugs: PaginatedDrugs
  stats: Record<string, number>
  filters: Record<string, any>
}

const props = defineProps<Props>()

/* UI state */
const filterButtons = [
  { key: 'sales', label: 'Sales' },
  { key: 'all', label: 'All Drug(s) In Store' },
  { key: 'active', label: 'Active Drug(s)' },
  { key: 'inactive', label: 'Inactive Drug(s)' },
  { key: 'low_stock', label: 'Low stock' },
  { key: 'expiring_soon', label: 'Expiring Soon' },
  { key: 'expired', label: 'Expired' },
  { key: 'pending_orders', label: 'Pending Orders' },
]

const activeFilter = ref(props.filters.stock_status || 'all')
const searchQuery = ref(props.filters.search || '')
const showDatePicker = ref(false)
const dateRange = ref({ start: '', end: '' })

/* compute safe pagination props */
const pagination = computed(() => {
  // support legacy shape or meta-shape
  const p = props.drugs
  return {
    current_page: p.current_page ?? p.meta?.current_page ?? 1,
    last_page: p.last_page ?? p.meta?.last_page ?? 1,
    total: p.total ?? p.meta?.total ?? (p.data?.length ?? 0)
  }
})

/* Columns per filter (keys must match drug object fields or be handled in formatters) */
const tableColumns = computed(() => {
  switch (activeFilter.value) {
    case 'sales':
      return [
        { key: 'id', label: '#' },
        { key: 'time', label: 'Time' },
        { key: 'drug_name', label: 'Drug' },
        { key: 'drug_dose', label: 'Dose' },
        { key: 'patient', label: 'Patient' },
        { key: 'source', label: 'Source' },
        { key: 'status', label: 'Status' },
      ]
    case 'low_stock':
      return [
        { key: 'id', label: 'ID' },
        { key: 'drug_name', label: 'Drug' },
        { key: 'drug_dose', label: 'Dose' },
        { key: 'total_sachets_in_stock', label: 'Qty Left' },
        { key: 'batch_number', label: 'Batch No.' },
        { key: 'expiry_date', label: 'Expiry Date' },
        { key: 'status', label: 'Status' },
      ]
    case 'expiring_soon':
    case 'expired':
      return [
        { key: 'id', label: 'ID' },
        { key: 'drug_name', label: 'Drug' },
        { key: 'drug_dose', label: 'Dose' },
        { key: 'expiry_date', label: 'Expiry Date' },
        { key: 'status', label: 'Status' },
      ]
    case 'pending_orders':
      return [
        { key: 'id', label: 'ID' },
        { key: 'date_of_order', label: 'Date' },
        { key: 'drug_name', label: 'Drug' },
        { key: 'drug_dose', label: 'Dose' },
        { key: 'patient', label: 'Patient' },
        { key: 'diagnosis', label: 'Diagnosis' },
        { key: 'source', label: 'Source' },
        { key: 'unit', label: 'Unit' },
        { key: 'ordered_by', label: 'Ordered By' },
        { key: 'consultant', label: 'Consultant' },
        { key: 'status', label: 'Status' },
      ]
    default:
      return [
        { key: 'id', label: 'ID' },
        { key: 'drug_name', label: 'Name' },
        { key: 'generic_name', label: 'Generic Name' },
        { key: 'dose', label: 'Dose/Unit' }, // special: uses drug_dose + dose_unit
        { key: 'drug_group_class', label: 'Group' },
        { key: 'max_daily_dose', label: 'High' },
        { key: 'min_daily_dose', label: 'Low' },
        { key: 'total_sachets_in_stock', label: 'Quantity' },
        { key: 'price_per_sachet', label: 'Price' },
        { key: 'batch_number', label: 'Code' },
        { key: 'status', label: 'Status' },
      ]
  }
})

/* Formatters map: colKey -> function(drug) => rendered string or VNode */
const columnFormatters: Record<string, (d: Drug, i?: number) => any> = {
  id: (d, i) => `${i !== undefined ? i + 1 : (d.id ?? '')}`,
  dose: (d: Drug) => {
    // prefer drug_dose + dose_unit
    if (d.drug_dose && d.dose_unit) return `${d.drug_dose}/${d.dose_unit}`
    return d.drug_dose ?? '—'
  },
  drug_dose: (d: Drug) => d.drug_dose ?? '—',
  price_per_sachet: (d: Drug) => (d.price_per_sachet != null ? `₦${Number(d.price_per_sachet).toLocaleString()}` : '—'),
  expiry_date: (d: Drug) => (d.expiry_date ? new Date(d.expiry_date).toLocaleDateString() : '—'),
  status: (d: Drug) => getStatusBadge(d).text,
  time: (d: Drug) => d.time ?? '—',
  patient: (d: Drug) => d.patient ?? '—',
  source: (d: Drug) => d.source ?? '—',
  date_of_order: (d: Drug) => (d.date_of_order ? new Date(d.date_of_order).toLocaleDateString() : '—'),
  // fallback will be handled by template if no formatter
}

/* Status badge generator (also used in the table cell classes) */
function getStatusBadge(drug: Drug) {
  if (drug.is_expired) return { class: 'bg-red-100 text-red-700', text: 'Expired' }
  if (drug.total_sachets_in_stock === 0) return { class: 'bg-gray-100 text-gray-700', text: 'Out of Stock' }
  if (drug.is_low_stock) return { class: 'bg-yellow-100 text-yellow-700', text: 'Low Stock' }
  return { class: 'bg-green-100 text-green-700', text: 'Active' }
}

/* Filter & router */
function setActiveFilter(key: string) {
  activeFilter.value = key
  applyFilters()
}

function applyFilters() {
  const filters: Record<string, any> = { search: searchQuery.value || undefined }

  switch (activeFilter.value) {
    case 'active': filters.stock_status = 'in_stock'; break
    case 'inactive': filters.stock_status = 'out_of_stock'; break
    case 'low_stock': filters.stock_status = 'low_stock'; break
    case 'expiring_soon': filters.expiry_status = 'expiring_soon'; break
    case 'expired': filters.expiry_status = 'expired'; break
    case 'pending_orders': filters.order_status = 'pending'; break
  }

  router.get(route('drugs.index'), filters, { preserveState: true, preserveScroll: true })
}

function handleSearch() { applyFilters() }

/* debugging helper to confirm shapes and missing keys */
function verifyPayload() {
  const example = props.drugs.data?.[0]
  if (!example) {
    console.warn('Drugs payload empty or missing props.drugs.data')
    return
  }

  // list of keys we expect to render
  const expectedKeys = new Set(tableColumns.value.map(c => c.key))
  const presentKeys = new Set(Object.keys(example))
  const missing = [...expectedKeys].filter(k => !(k in example) && !(k in columnFormatters))
  if (missing.length) {
    console.warn('Some expected keys are missing in the first item:', missing, { example })
  } else {
    console.info('Payload keys OK for this view. Example item:', example)
  }
}

onMounted(() => verifyPayload())
</script>

<template>
  <AppLayout title="Drug Inventory">
    <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
      <!-- header -->
      <div class="flex justify-between items-center gap-4">
        <div class="flex-1 max-w-md">
          <div class="relative">
            <input v-model="searchQuery" @keyup.enter="handleSearch" type="text" placeholder="Search drug name..." class="w-full pl-4 pr-12 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-teal-400" />
            <button @click="handleSearch" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 bg-teal-500 rounded-full flex items-center justify-center text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
          </div>
        </div>

        <Link :href="route('drugs.create')" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">+ Add Drug</Link>
      </div>

      <!-- filter buttons -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <button v-for="btn in filterButtons" :key="btn.key" @click="setActiveFilter(btn.key)"
          class="py-4 rounded-xl font-medium text-sm transition-all"
          :class="activeFilter === btn.key ? 'bg-teal-100 text-teal-700 shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
          {{ btn.label }}
        </button>
      </div>

      <!-- table -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-for="col in tableColumns" :key="col.key" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ col.label }}</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              <tr v-for="(drug, rowIndex) in props.drugs.data" :key="drug.id" class="hover:bg-gray-50 transition">
                <!-- render each column using formatters or fallback -->
                <td v-for="(col, colIndex) in tableColumns" :key="col.key" class="px-6 py-4 text-sm text-gray-700">
                  <template v-if="columnFormatters[col.key]">
                    {{ columnFormatters[col.key](drug, rowIndex) }}
                  </template>

                  <template v-else-if="col.key === 'status'">
                    <span :class="getStatusBadge(drug).class" class="px-3 py-1 rounded-full text-xs font-medium">
                      {{ getStatusBadge(drug).text }}
                    </span>
                  </template>

                  <template v-else>
                    {{ drug[col.key] ?? '—' }}
                  </template>
                </td>
              </tr>

              <tr v-if="!(props.drugs.data?.length)">
                <td :colspan="tableColumns.length" class="px-6 py-12 text-center text-gray-400">
                  <div class="flex flex-col items-center gap-2">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6"/></svg>
                    <p class="text-lg">No records found</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- pagination -->
        <div v-if="props.drugs.data?.length" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">Showing {{ props.drugs.data.length }} of {{ pagination.total }} results</div>
          <div class="flex gap-2">
            <Link v-if="pagination.current_page > 1" :href="route('drugs.index', { page: pagination.current_page - 1 })" class="px-4 py-2 border rounded-lg text-sm text-gray-700">Previous</Link>
            <Link v-if="pagination.current_page < (props.drugs.last_page ?? pagination.last_page)" :href="route('drugs.index', { page: pagination.current_page + 1 })" class="px-4 py-2 border rounded-lg text-sm text-gray-700">Next</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* small visual helpers */
</style>
