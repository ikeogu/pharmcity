<script setup lang="ts">
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { User } from '@/types'

interface FormData {
  title: string
  first_name: string
  last_name: string
  username: string
  email: string
  phone: string
  dob: string
  gender: string
  address: string
  role_id: string | number
  country_id: string
  state_id: string
  city_id: string
}

const props = defineProps<{
  errors: Record<string, string>
  roles: Array<{ id: number; name: string }>
  countries: Array<{ id: string; name: string }>
  user: { data: User }
}>()

const states = ref<Array<{ id: string; name: string }>>([])
const cities = ref<Array<{ id: string; name: string }>>([])
const showFlash = ref(false)

// Store original user data for comparison
const originalData = ref<FormData>({
  title: '',
  first_name: '',
  last_name: '',
  username: '',
  email: '',
  phone: '',
  dob: '',
  gender: '',
  address: '',
  role_id: '',
  country_id: '',
  state_id: '',
  city_id: '',
})

// Get flash messages from Inertia page props
const page = usePage()
const flash = computed(() => (page.props.flash as { success?: string; error?: string }) || {})

// Initialize form with user data
const form = useForm<FormData>({
  title: props.user.data?.title || '',
  first_name: props.user.data?.first_name || '',
  last_name: props.user.data?.last_name || '',
  username: props.user.data?.username || '',
  email: props.user.data?.email || '',
  phone: props.user.data?.phone || '',
  dob: props.user.data?.dob || '',
  gender: props.user.data?.gender || '',
  address: props.user.data?.address || '',
  role_id: props.user.data?.role_id || '',
  country_id: props.user.data?.country_id || '',
  state_id: props.user.data?.state_id || '',
  city_id: props.user.data?.city_id || '',
})

// Store original data
originalData.value = {
  title: props.user.data?.title || '',
  first_name: props.user.data?.first_name || '',
  last_name: props.user.data?.last_name || '',
  username: props.user.data?.username || '',
  email: props.user.data?.email || '',
  phone: props.user.data?.phone || '',
  dob: props.user.data?.dob || '',
  gender: props.user.data?.gender || '',
  address: props.user.data?.address || '',
  role_id: props.user.data?.role_id || '',
  country_id: props.user.data?.country_id || '',
  state_id: props.user.data?.state_id || '',
  city_id: props.user.data?.city_id || '',
}

// Fetch states when country changes
async function fetchStates() {
  form.state_id = ''
  form.city_id = ''
  states.value = []
  cities.value = []

  if (!form.country_id) return

  try {
    const res = await axios.get(`/fetch-states/${form.country_id}`)
    states.value = res.data.data
  } catch (error) {
    console.error('Error fetching states:', error)
  }
}

// Fetch cities when state changes
async function fetchCities() {
  form.city_id = ''
  cities.value = []

  if (!form.state_id) return

  try {
    const res = await axios.get(`/fetch-cities/${form.state_id}`)
    cities.value = res.data.data
  } catch (error) {
    console.error('Error fetching cities:', error)
  }
}

// Load existing states and cities on mount
onMounted(async () => {
  // Show flash message if exists
  if (flash.value?.success || flash.value?.error) {
    showFlash.value = true
    setTimeout(() => {
      showFlash.value = false
    }, 5000)
  }

  if (form.country_id) {
    try {
      const res = await axios.get(`/fetch-states/${form.country_id}`)
      states.value = res.data.data
    } catch (error) {
      console.error('Error loading states:', error)
    }
  }

  if (form.state_id) {
    try {
      const res = await axios.get(`/fetch-cities/${form.state_id}`)
      cities.value = res.data.data
    } catch (error) {
      console.error('Error loading cities:', error)
    }
  }
})

// Submit handler
function submit() {
  // Get only the fields that have changed
  const changedData: Record<string, any> = {}

  // Type-safe iteration over form keys
  const formKeys = Object.keys(form.data()) as Array<keyof FormData>

  formKeys.forEach((key) => {
    if (form[key] !== originalData.value[key]) {
      changedData[key] = form[key] as any
    }
  })

  // If nothing changed, show a message
  if (Object.keys(changedData).length === 0) {
    alert('No changes detected')
    return
  }

  // Submit only changed fields
  form.transform(() => changedData).put(route('users.update', { user: props.user.data.id }), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ User updated successfully')
      // Update original data after successful save
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
  })
}
</script>

<template>
  <!-- Keep the same template as before -->
  <AppLayout title="Edit Staff">
    <div class="max-w-5xl mx-auto px-6 py-8">
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
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Edit Staff</h1>
          <p class="text-gray-500">Update user information</p>
        </div>

        <Link
          :href="route('users.index')"
          class="text-indigo-600 hover:text-indigo-800 flex items-center space-x-1"
        >
          <span class="text-sm">← Back to Staff List</span>
        </Link>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm p-8">
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input
              v-model="form.title"
              type="text"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</p>
          </div>

          <!-- Username -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input
              v-model="form.username"
              type="text"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="errors.username" class="text-red-500 text-sm mt-1">{{ errors.username }}</p>
          </div>

          <!-- First Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
            <input
              v-model="form.first_name"
              type="text"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              required
            />
            <p v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</p>
          </div>

          <!-- Last Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
            <input
              v-model="form.last_name"
              type="text"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              required
            />
            <p v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              required
            />
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input
              v-model="form.phone"
              type="text"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
          </div>

          <!-- Date of Birth -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
            <input
              v-model="form.dob"
              type="date"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="errors.dob" class="text-red-500 text-sm mt-1">{{ errors.dob }}</p>
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
            <select
              v-model="form.gender"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option value="">Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
            <p v-if="errors.gender" class="text-red-500 text-sm mt-1">{{ errors.gender }}</p>
          </div>

          <!-- Address -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea
              v-model="form.address"
              rows="2"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            ></textarea>
            <p v-if="errors.address" class="text-red-500 text-sm mt-1">{{ errors.address }}</p>
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select
              v-model="form.role_id"
              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              required
            >
              <option value="" disabled>Select role</option>
              <option
                v-for="role in roles"
                :key="role.id"
                :value="role.id"
              >
                {{ role.name }}
              </option>
            </select>
            <p v-if="errors.role_id" class="text-red-500 text-sm mt-1">{{ errors.role_id }}</p>
          </div>

          <!-- Country, State, City -->
          <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Country -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
              <select
                v-model="form.country_id"
                @change="fetchStates"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                required
              >
                <option value="" disabled>Select Country</option>
                <option v-for="country in countries" :key="country.id" :value="country.id">
                  {{ country.name }}
                </option>
              </select>
              <p v-if="errors.country_id" class="text-red-500 text-sm mt-1">{{ errors.country_id }}</p>
            </div>

            <!-- State -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
              <select
                v-model="form.state_id"
                @change="fetchCities"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                required
                :disabled="!states.length"
              >
                <option value="" disabled>Select State</option>
                <option v-for="state in states" :key="state.id" :value="state.id">
                  {{ state.name }}
                </option>
              </select>
              <p v-if="errors.state_id" class="text-red-500 text-sm mt-1">{{ errors.state_id }}</p>
            </div>

            <!-- City -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
              <select
                v-model="form.city_id"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                required
                :disabled="!cities.length"
              >
                <option value="" disabled>Select City</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }}
                </option>
              </select>
              <p v-if="errors.city_id" class="text-red-500 text-sm mt-1">{{ errors.city_id }}</p>
            </div>
          </div>

          <!-- Submit -->
          <div class="md:col-span-2 flex justify-end mt-6">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
            >
              {{ form.processing ? 'Updating...' : 'Update Staff' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
