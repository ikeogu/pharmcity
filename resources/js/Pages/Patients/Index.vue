<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  IconPlus,
  IconUpload,
  IconDownload,
  IconRefresh,
  IconSearch
} from '@tabler/icons-vue'

// ---------------------
// Types
// ---------------------
interface Patient {
  id: number
  hospital_id: string
  phone: string
  email: string | null
  first_name: string
  middle_name?: string | null
  last_name: string
  patient_type: string
  payment_party: string
  status: string
  registered_at: string
  full_name: string
}

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface Paginated<T> {
  data: T[]
  current_page: number
  from: number
  to: number
  total: number
  links: PaginationLink[]
}

interface FilterParams {
  search?: string
  status?: string
  patient_type?: string
  payment_party?: string
  sort_by?: string
  sort_order?: string
}

// ---------------------
// Props
// ---------------------
const props = defineProps<{
  patients: Paginated<Patient>
  filters: FilterParams
}>()

// ---------------------
// Reactive State
// ---------------------
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const patientType = ref(props.filters.patient_type ?? '')
const paymentParty = ref(props.filters.payment_party ?? '')
const sortBy = ref(props.filters.sort_by ?? 'created_at')
const sortOrder = ref(props.filters.sort_order ?? 'desc')

// ---------------------
// Computed & Watchers
// ---------------------
const exportUrl = computed(() =>
  route('patients.export', {
    search: search.value,
    status: status.value,
    patient_type: patientType.value,
    payment_party: paymentParty.value,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  })
)

watch([search, status, patientType, paymentParty, sortBy, sortOrder], () => {
  router.get(
    route('patients.index'),
    {
      search: search.value,
      status: status.value,
      patient_type: patientType.value,
      payment_party: paymentParty.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    },
    { preserveState: true, replace: true }
  )
})

const refresh = (): void => router.reload({ preserveState: true })

// ---------------------
// Import Modal
// ---------------------
const showImportModal = ref(false)
const selectedFile = ref<File | null>(null)
const loading = ref(false)

const openImportModal = () => (showImportModal.value = true)
const closeImportModal = () => {
  showImportModal.value = false
  selectedFile.value = null
}

const handleFileChange = (event: Event): void => {
  const files = (event.target as HTMLInputElement).files
  if (files && files[0]) {
    selectedFile.value = files[0]
  }
}

const handleImport = (): void => {
  if (!selectedFile.value) {
    alert('Please select a file.')
    return
  }

  loading.value = true
  const formData = new FormData()
  formData.append('file', selectedFile.value)

  router.post(route('patients.import'), formData, {
    onFinish: () => {
      loading.value = false
      closeImportModal()
    },
    onSuccess: () => alert('Patients imported successfully!'),
    onError: () => alert('Import failed. Check file format.'),
  })
}

const exportPatients = (): void => {
  window.location.href = exportUrl.value
}
</script>

<template>
    <AppLayout title="Drug Inventory">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Patients</h1>
                <p class="text-sm text-gray-500">Manage and view all registered patients</p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <Link
                :href="route('patients.create')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2 transition"
                >
                <IconPlus :size="18" /> Add Patient
                </Link>

                <button
                @click="openImportModal"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center gap-2 transition"
                >
                <IconUpload :size="18" /> Import Excel
                </button>

                <button
                @click="exportPatients"
                class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md flex items-center gap-2 transition"
                >
                <IconDownload :size="18" /> Export
                </button>

                <button
                @click="refresh"
                class="p-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition"
                title="Refresh"
                >
                <IconRefresh :size="18" class="text-gray-600" />
                </button>
            </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 bg-white p-4 rounded-lg shadow-sm border">
            <div class="col-span-2 flex items-center gap-2 border rounded-lg px-3">
                <IconSearch :size="16" class="text-gray-400" />
                <input
                v-model="search"
                type="text"
                placeholder="Search name or phone..."
                class="w-full border-none focus:ring-0 text-sm py-2"
                />
            </div>

            <select v-model="status" class="border rounded-lg py-2 px-3 text-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>

            <select v-model="patientType" class="border rounded-lg py-2 px-3 text-sm">
                <option value="">All Types</option>
                <option value="inpatient">Inpatient</option>
                <option value="outpatient">Outpatient</option>
            </select>

            <select v-model="paymentParty" class="border rounded-lg py-2 px-3 text-sm">
                <option value="">Payment Party</option>
                <option value="self">Self</option>
                <option value="insurance">Insurance</option>
            </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow-sm border">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Full Name</th>
                    <th class="px-4 py-3 text-left">Hospital ID</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Registered</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
                </thead>

                <tbody>
                <tr
                    v-for="(patient, index) in props.patients.data"
                    :key="patient.id"
                    class="border-t hover:bg-gray-50 transition"
                >
                    <td class="px-4 py-3">{{ index + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ patient.full_name }}</td>
                    <td class="px-4 py-3">{{ patient.hospital_id }}</td>
                    <td class="px-4 py-3">{{ patient.phone }}</td>
                    <td class="px-4 py-3 capitalize">{{ patient.patient_type }}</td>
                    <td class="px-4 py-3">
                    <span
                        :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        patient.status === 'active'
                            ? 'bg-green-100 text-green-700'
                            : patient.status === 'pending'
                            ? 'bg-yellow-100 text-yellow-700'
                            : 'bg-gray-100 text-gray-600',
                        ]"
                    >
                        {{ patient.status }}
                    </span>
                    </td>
                    <td class="px-4 py-3">{{ patient.registered_at }}</td>
                    <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <Link
                        :href="route('patients.show', patient.id)"
                        class="text-blue-600 hover:underline"
                        >
                        View
                        </Link>
                        <Link
                        :href="route('patients.edit', patient.id)"
                        class="text-yellow-600 hover:underline"
                        >
                        Edit
                        </Link>
                    </div>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-4 text-sm text-gray-600 gap-3">
            <p>
                Showing
                <span class="font-semibold">{{ props.patients.from }}</span>
                to
                <span class="font-semibold">{{ props.patients.to }}</span>
                of
                <span class="font-semibold">{{ props.patients.total }}</span>
                results
            </p>

            <div class="flex gap-1">
            <!--  <Link
                v-for="link in props.patients.links"
                :key="link.label"
                v-html="link.label"
                :href="link.url ?? '#'"
                @click.prevent="link.url && router.visit(link.url)"
                class="px-3 py-1 border rounded-md text-sm transition"
                :class="{
                    'bg-blue-600 text-white border-blue-600': link.active,
                    'text-gray-600 hover:bg-gray-100': !link.active && link.url,
                    'opacity-50 cursor-not-allowed': !link.url,
                }"
                /> -->
            </div>
            </div>

            <!-- Import Modal -->
            <Transition name="fade">
            <div
                v-if="showImportModal"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
                <h2 class="text-lg font-semibold mb-4">Batch Import Patients</h2>

                <form @submit.prevent="handleImport">
                    <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    @change="handleFileChange"
                    class="border border-gray-300 rounded-md w-full p-2 mb-4"
                    />

                    <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeImportModal"
                        class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ loading ? 'Importing...' : 'Import' }}
                    </button>
                    </div>
                </form>
                </div>
            </div>
            </Transition>
        </div>
    </AppLayout>
</template>
