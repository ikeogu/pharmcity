<script setup lang="ts">
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Save, ArrowLeft } from 'lucide-vue-next'
import AppLayout from '@/Layouts/AppLayout.vue'

// --------------------------------------
// Props
// --------------------------------------
interface Patient {
  id: number
  hospital_id: string
  phone: string
  email: string
  title: string
  first_name: string
  middle_name: string
  last_name: string
  dob: string
  gender: string
  additional_details: string
  registration_id: string
  service_location_id: string
  unit_id: string
  consultant_id: string
  patient_type: string
  payment_party: string
  permanent_address: string
  city: string
  state: string
  zipcode: string
  nationality: string
  occupation: string
  marital_status: string
  religion: string
  nok_full_name: string
  nok_phone: string
  nok_address: string
  nok_relationship: string
  nok_occupation: string
  nok_gender: string
  status: string
  full_name: string
}

interface Props {
  patient: Patient
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

const props = defineProps<{
  patient: Patient | { data: Patient }
}>()


// 🧠 Fix: Normalize data so it's always a Patient object
const patient = computed<Patient>(() => {
  return (props.patient as any).data ?? props.patient
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
  hospital_id: patient.hospital_id || '',
  phone: patient.phone || '',
  email: patient.email || '',
  title: patient.title || '',
  first_name: patient.first_name || '',
  middle_name: patient.middle_name || '',
  last_name: patient.last_name || '',
  dob: patient.dob || '',
  gender: patient.gender || '',
  additional_details: patient.additional_details || '',
  registration_id: patient.registration_id || '',
  service_location_id: patient.service_location_id || '',
  unit_id: patient.unit_id || '',
  consultant_id: patient.consultant_id || '',
  patient_type: patient.patient_type || 'NEW',
  payment_party: patient.payment_party || 'Self/General',
  permanent_address: patient.permanent_address || '',
  city: patient.city || '',
  state: patient.state || '',
  zipcode: patient.zipcode || '',
  nationality: patient.nationality || '',
  occupation: patient.occupation || '',
  marital_status: patient.marital_status || '',
  religion: patient.religion || '',
  nok_full_name: patient.nok_full_name || '',
  nok_phone: patient.nok_phone || '',
  nok_address: patient.nok_address || '',
  nok_relationship: patient.nok_relationship || '',
  nok_occupation: patient.nok_occupation || '',
  nok_gender: patient.nok_gender || '',
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
    if (form[key] !== originalData.value[key]) {
      changedData[key] = form[key]
    }
  })

  // If nothing changed
  if (Object.keys(changedData).length === 0) {
    alert('No changes detected')
    return
  }

  console.log('Sending changed data:', changedData)

  form.transform(() => changedData).put(route('patients.update', patient.id), {
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
            Update {{ patient.full_name }}'s information • ID: {{ patient.hospital_id }}
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

        <!-- Section: Registration Information -->
        <div>
          <h2 class="font-semibold text-xl text-gray-800 mb-4 pb-2 border-b-2 border-teal-400">
            REGISTRATION INFORMATION
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Select Registration -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Registration</label>
              <select
                v-model="form.registration_id"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Registration</option>
                <option v-for="reg in registrationTypes" :key="reg.id" :value="reg.id">
                  {{ reg.name }}
                </option>
              </select>
            </div>

            <!-- Select Service Location -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Service Location</label>
              <select
                v-model="form.service_location_id"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Service Location</option>
                <option v-for="loc in serviceLocations" :key="loc.id" :value="loc.id">
                  {{ loc.name }}
                </option>
              </select>
            </div>

            <!-- Select Unit -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Unit</label>
              <select
                v-model="form.unit_id"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Unit</option>
                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>

            <!-- Select Consultant -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Consultant</label>
              <select
                v-model="form.consultant_id"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Consultant</option>
                <option v-for="consultant in consultants" :key="consultant.id" :value="consultant.id">
                  {{ consultant.first_name }} {{ consultant.last_name }}
                </option>
              </select>
            </div>

            <!-- Select Patient Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Select Patient Type <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.patient_type"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              >
                <option value="">Select Patient Type</option>
                <option v-for="type in patientTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>

            <!-- Select Payment Party -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Select Payment Party <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.payment_party"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
                required
              >
                <option value="">Select Payment Party</option>
                <option v-for="party in paymentParties" :key="party" :value="party">{{ party }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Section: Contact Information -->
        <div>
          <h2 class="font-semibold text-xl text-gray-800 mb-4 pb-2 border-b-2 border-teal-400">
            CONTACT INFORMATION
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Permanent Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Permanent Address</label>
              <input
                v-model="form.permanent_address"
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

            <!-- Zipcode -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Zipcode</label>
              <input
                v-model="form.zipcode"
                type="text"
                placeholder="552542"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- Nationality -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
              <input
                v-model="form.nationality"
                type="text"
                placeholder="e.g., Nigeria"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- Occupation -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
              <input
                v-model="form.occupation"
                type="text"
                placeholder="Business Man"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              />
            </div>

            <!-- Marital Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Marital Status</label>
              <select
                v-model="form.marital_status"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Marital Status</option>
                <option v-for="status in maritalStatuses" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
            </div>

            <!-- Religion -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Religion</label>
              <select
                v-model="form.religion"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-400 focus:ring focus:ring-teal-200"
              >
                <option value="">Select Religion</option>
                <option value="Christianity">Christianity</option>
                <option value="Islam">Islam</option>
                <option value="Traditional">Traditional</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Section: Next of Kin Information -->
        <div>
          <h2 class="font-semibold text-xl text-gray-800 mb-4 pb-2 border-b-2 border-teal-400">
            NEXT-OF-KIN INFORMATION
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NOK Full Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Full Name</label>
              <input
                v-model="form.nok_full_name"
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

            <!-- NOK Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Address</label>
              <input
                v-model="form.nok_address"
                type="text"
                placeholder="e.g., +234 802 345 6789"
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

            <!-- NOK Occupation -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Next-of-Kin Occupation</label>
              <input
                v-model="form.nok_occupation"
                type="text"
                placeholder="e.g., Lawyer"
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
