<template>
  <AppLayout>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Add New Staff</h1>
      <p class="text-gray-500">Create a new staff member and assign a role</p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
      <form @submit.prevent="submitForm" class="space-y-4">

        <div class="flex space-x-4">
            <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1" for="first_name">Title</label>
            <input
                v-model="form.title"
                id="title"
                type="text"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
            />
            </div>

            <div class="flex-1">
                <label class="block text-gray-700 font-medium mb-1" for="role">Role</label>
                <select
                    v-model="form.role_id"
                    id="role"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
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
            </div>
        </div>

        <div class="flex space-x-4">
             <!-- First Name -->
            <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1" for="first_name">First Name</label>
            <input
                v-model="form.first_name"
                id="first_name"
                type="text"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                required
            />
            </div>

            <!-- Last Name -->
            <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1" for="last_name">Last Name</label>
            <input
                v-model="form.last_name"
                id="last_name"
                type="text"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                required
            />
            </div>
        </div>


        <!-- Email -->
        <div>
          <label class="block text-gray-700 font-medium mb-1" for="email">Email</label>
          <input
            v-model="form.email"
            id="email"
            type="email"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
            required
          />
        </div>

        <div class="flex space-x-4">
             <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1" for="role">Gender</label>
            <select
                v-model="form.gender"
                id="gender"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                required
            >
                <option value="" disabled>Select Gender</option>
                <option value="female">Female</option>
                <option value="male">Male</option>
            </select>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-gray-700 font-medium mb-1">Date of Birth</label>
                <input
                type="date"
                v-model="form.dob"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                required
                />
            </div>
        </div>

         <div>
          <label class="block text-gray-700 font-medium mb-1" for="email">Address</label>
          <input
            v-model="form.address"
            id="address"
            type="text"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"

          />
        </div>

        <!-- Role Selection -->
       <div class="flex space-x-4">
        <!-- Country -->
        <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1">Country</label>
            <select
            v-model="form.country_id"
            @change="fetchStates"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
            required
            >
            <option value="" disabled>Select Country</option>
            <option v-for="country in countries" :key="country.id" :value="country.id">
                {{ country.name }}
            </option>
            </select>
        </div>

        <!-- State -->
        <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1">State</label>
            <select
            v-model="form.state_id"
            @change="fetchCities"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
            required
            :disabled="!states.length"
            >
            <option value="" disabled>Select State</option>
            <option v-for="state in states" :key="state.id" :value="state.id">
                {{ state.name }}
            </option>
            </select>
        </div>

        <!-- City -->
        <div class="flex-1">
            <label class="block text-gray-700 font-medium mb-1">City</label>
            <select
            v-model="form.city_id"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
            required
            :disabled="!cities.length"
            >
            <option value="" disabled>Select City</option>
            <option v-for="city in cities" :key="city.id" :value="city.id">
                {{ city.name }}
            </option>
            </select>
        </div>
        </div>


        <!-- Password -->
       <div class="grid grid-cols-3 gap-4">
            <!-- Password -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input
                type="password"
                v-model="form.password"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter password"
                required
                />
                <p v-if="errors.password" class="text-red-500 text-sm mt-1">
                {{ errors.password }}
                </p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Confirm Password</label>
                <input
                type="password"
                v-model="form.password_confirmation"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="Confirm password"
                required
                />
            </div>
            </div>



        <!-- Submit Button -->
        <div class="pt-4">
          <button
            type="submit"
            class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition"
            :disabled="processing"
          >
            {{ processing ? 'Saving...' : 'Create Staff' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'
import axios from 'axios'

const props = defineProps<{
    errors: Record<string, string>
    roles: Array<{ id: number; name: string }>
    countries : Array<{ id: string; name: string }>
}>()

// Form reactive state
const form = reactive({
    title: '',
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    role_id: '',
    country_id: '',
    state_id: '',
    city_id: '',
    dob: '',
    gender: '',  // ✅ Added gender field
    address: '',
    password_confirmation : ''
})

const states = ref<Array<{ id: number; name: string }>>([])
const cities = ref<Array<{ id: number; name: string }>>([])

const processing = ref(false)

// Submit function
function submitForm() {
  processing.value = true

  router.post(route('users.store'), form, {
    onFinish: () => (processing.value = false),
    onSuccess: () => {
      // Optionally reset form after creation
      form.title = ''
      form.first_name = ''
      form.last_name = ''
      form.email = ''
      form.password = ''
      form.role_id = ''
      form.country_id = ''
      form.state_id = ''
      form.city_id = ''
      form.dob = ''
      form.gender = ''
      form.address = ''
      form.password_confirmation = ''
    },
  })
}

// Fetch states when country changes
async function fetchStates() {
  form.state_id = ''
  form.city_id = ''
  cities.value = []

  if (!form.country_id) return

  try {
    const res = await axios.get(`/fetch-states/${form.country_id}`)
    states.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}

// Fetch cities when state changes
async function fetchCities() {
  form.city_id = ''

  if (!form.state_id) return

  try {
    const res = await axios.get(`/fetch-cities/${form.state_id}`)
    cities.value = res.data.data
  } catch (error) {
    console.error(error)
  }
}
</script>
