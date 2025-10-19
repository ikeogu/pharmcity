<script setup lang="ts">
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Save, ArrowLeft } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Patient } from '@/types'

// --------------------------------------
// Props
// --------------------------------------
interface Props {
  patient: Patient | { data: Patient }
  titles: string[]
  genders: string[]
  patientTypes: string[]
  paymentParties: string[]
  maritalStatuses: string[]
  consultants: Array<{ id: number; first_name: string; last_name: string }>
  serviceLocations?: Array<{ id: number; name: string }>
  units?: Array<{ id: number; name: string }>
  registrationTypes?: Array<{ id: number; name: string }>
  errors?: Record<string, string>
}

const props = defineProps<Props>()

// Type guard to check if patient has data property
function isPatientWrapper(patient: Patient | { data: Patient }): patient is { data: Patient } {
  return 'data' in patient && patient.data !== undefined
}

// 🧠 Fix: Normalize data so it's always a Patient object
const patient = computed<Patient>(() => {
  return isPatientWrapper(props.patient) ? props.patient.data : props.patient
})

// Get flash messages
const page = usePage()
const flash = computed(() => (page.props.flash as { success?: string; error?: string }) || {})
const showFlash = ref(false)

// Store original data for comparison
const originalData = ref<Record<string, any>>({})

// --------------------------------------
// Form Setup
// --------------------------------------
const form = useForm({
  hospital_id: patient.value.hospital_id || '',
  phone: patient.value.phone || '',
  email: patient.value.email || '',
  title: patient.value.title || '',
  first_name: patient.value.first_name || '',
  middle_name: patient.value.middle_name || '',
  last_name: patient.value.last_name || '',
  dob: patient.value.dob || '',
  gender: patient.value.gender || '',
  additional_details: patient.value.additional_details || '',
  registration_id: patient.value.registration_id || '',
  address: patient.value.address || '',
  city: patient.value.city || '',
  state: patient.value.state || '',
  country: patient.value.country || '',
  nok_name: patient.value.nok_name || '',
  nok_phone: patient.value.nok_phone || '',
  nok_email: patient.value.nok_email || '',
  nok_address: patient.value.nok_address || '',
  nok_relationship: patient.value.nok_relationship || '',
  nok_gender: patient.value.nok_gender || '',
})

// Store original data
originalData.value = { ...form.data() }

// Show flash messages
if (flash.value?.success || flash.value?.error) {
  showFlash.value = true
  setTimeout(() => {
    showFlash.value = false
  }, 5000)
}

const submit = () => {
  // Get only changed fields
  const changedData: Record<string, any> = {}

  Object.keys(form.data()).forEach((key) => {
    if (form[key as keyof typeof form.data] !== originalData.value[key]) {
      changedData[key] = form[key as keyof typeof form.data]
    }
  })

  // If nothing changed
  if (Object.keys(changedData).length === 0) {
    alert('No changes detected')
    return
  }

  console.log('Sending changed data:', changedData)

  form.transform(() => changedData).put(route('patients.update', patient.value.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      console.log('✅ Patient updated successfully')
      Object.assign(originalData.value, changedData)
      showFlash.value = true
      setTimeout(() => {
        showFlash.value = false
      }, 5000)
    },
    onError: (errors) => {
      console.error('❌ Update failed:', errors)
      showFlash.value = true
      setTimeout(() => {
        showFlash.value = false
      }, 5000)
    },
    onFinish: () => {
      form.transform((data) => data)
    },
  })
}

// Computed property for full name
const fullName = computed(() =>
  [patient.value.title, patient.value.first_name, patient.value.middle_name, patient.value.last_name]
    .filter(Boolean)
    .join(' ')
)
</script>

<template>
  <AppLayout title="Edit Patient">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <!-- Flash Messages -->
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform translate-y-2"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showFlash && flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ flash?.success }}</span>
          </div>
          <button @click="showFlash = false" class="text-green-600 hover:text-green-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </Transition>

      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform translate-y-2"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showFlash && flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>{{ flash?.error }}</span>
          </div>
          <button @click="showFlash = false" class="text-red-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Edit Patient</h1>
          <p class="text-gray-500 text-sm">
            Update {{ fullName }}'s information • ID: {{ patient.hospital_id }}
          </p>
        </div>
        <Link
          :href="route('patients.index')"
          class="inline-flex items-center gap-2 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition"
        >
          <ArrowLeft class="w-4 h-4" /> Back to Patients
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border p-8 space-y-8">

        <!-- Section: Patient Biodata -->
        <div>
          <h2 class="font-semibold text-xl text-gray-800 mb-4 pb-2 border-b-2 border-teal-400">
            PATIENT BIODATA
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Hospital ID (Disabled) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Hospital ID
              </label>
              <input
                v-model="form.hospital_id"
                type="text"
                class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50 cursor-not-allowed"
                disabled
              />
            </div>

            <!-- Phone Number -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Phone Number <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="e.g., +234 803 123 4567"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              />
              <span v-if="errors?.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</span>
            </div>

            <!-- Email Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="e.g., myemail@email.com"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- Gender -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Gender <span class="text-red-500">*</span>
              </label>
              <div class="flex gap-6 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="form.gender"
                    type="radio"
                    value="male"
                    class="text-teal-500 focus:ring-teal-400"
                  />
                  <span class="text-sm text-gray-700">Male</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="form.gender"
                    type="radio"
                    value="female"
                    class="text-teal-500 focus:ring-teal-400"
                  />
                  <span class="text-sm text-gray-700">Female</span>
                </label>
              </div>
            </div>

            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Select Title <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.title"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              >
                <option value="">Select Title</option>
                <option v-for="title in titles" :key="title" :value="title">{{ title }}</option>
              </select>
            </div>

            <!-- First Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Enter First Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.first_name"
                type="text"
                placeholder="David"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              />
            </div>

            <!-- Middle Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Enter Middle Name</label>
              <input
                v-model="form.middle_name"
                type="text"
                placeholder="Nnamdi"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- Last Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Enter Last Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.last_name"
                type="text"
                placeholder="Bello"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              />
            </div>

            <!-- Date of Birth -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Date Of Birth <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.dob"
                type="date"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              />
            </div>

            <!-- Additional Details -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Additional Details</label>
              <input
                v-model="form.additional_details"
                type="text"
                placeholder="e.g., Secondary phone number"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>
          </div>
        </div>

        <!-- Section: Contact Information -->
        <div>
          <h2 class="font-semibold text-xl text-gray-800 mb-4 pb-2 border-b-2 border-teal-400">
            CONTACT INFORMATION
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <input
                v-model="form.address"
                type="text"
                placeholder="e.g., No 52, Agbowo Street"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- City Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City Name</label>
              <input
                v-model="form.city"
                type="text"
                placeholder="e.g., Festac"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- State Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State Name</label>
              <input
                v-model="form.state"
                type="text"
                placeholder="e.g., Lagos"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- Country -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
              <input
                v-model="form.country"
                type="text"
                placeholder="e.g., Nigeria"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>
          </div>
        </div>

        <!-- Section: Next of Kin Information -->
        <div>
          <h2 class="font-semibold text-xl text-gray-800 mb-4 pb-2 border-b-2 border-teal-400">
            NEXT-OF-KIN INFORMATION
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NOK Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Name</label>
              <input
                v-model="form.nok_name"
                type="text"
                placeholder="e.g., Adetola Bello"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- NOK Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Phone Number</label>
              <input
                v-model="form.nok_phone"
                type="text"
                placeholder="e.g., +234 802 345 6789"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- NOK Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Email</label>
              <input
                v-model="form.nok_email"
                type="email"
                placeholder="e.g., nok@email.com"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- NOK Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Address</label>
              <input
                v-model="form.nok_address"
                type="text"
                placeholder="e.g., 123 Main St"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- NOK Relationship -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Relationship</label>
              <input
                v-model="form.nok_relationship"
                type="text"
                placeholder="e.g., Sister"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- NOK Gender -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Gender</label>
              <select
                v-model="form.nok_gender"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4 pt-6 border-t">
          <Link
            :href="route('patients.show', patient.id)"
            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium shadow-lg"
          >
            <Save class="w-5 h-5" />
            {{ form.processing ? 'Updating...' : 'Update Patient' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
