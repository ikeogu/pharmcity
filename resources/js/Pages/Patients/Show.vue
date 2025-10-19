<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { Patient } from '@/types'
import {
  IconEdit,
  IconUserPlus,
  IconCalendarPlus,
  IconPrescription,
  IconChevronLeft,
} from '@tabler/icons-vue'

const props = defineProps<{
  patient: Patient | { data: Patient }
}>()

// Type guard to check if patient has data property
function isPatientWrapper(patient: Patient | { data: Patient }): patient is { data: Patient } {
  return 'data' in patient && patient.data !== undefined
}

// 🧠 Fix: Normalize data so it's always a Patient object
const patient = computed<Patient>(() => {
  return isPatientWrapper(props.patient) ? props.patient.data : props.patient
})

// full name
const fullName = computed(() =>
  [patient.value.title, patient.value.first_name, patient.value.middle_name, patient.value.last_name]
    .filter(Boolean)
    .join(' ')
)
</script>

<template>
  <AppLayout title="Patient Details">
    <Head title="Patient Profile" />

    <div class="p-6 space-y-6">
      <!-- Back and Actions -->
      <div class="flex justify-between items-center">
        <Link
          :href="route('patients.index')"
          class="flex items-center text-gray-600 hover:text-blue-600"
        >
          <IconChevronLeft class="w-5 h-5 mr-1" /> Back to Patients
        </Link>

        <div class="flex gap-3">
          <button
            @click="router.visit(route('patients.edit', patient.id))"
            class="flex items-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-all"
          >
            <IconEdit class="w-5 h-5 mr-2" /> Edit
          </button>
          <button
            class="flex items-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-all"
          >
            <IconUserPlus class="w-5 h-5 mr-2" /> Add Family
          </button>
          <button
            class="flex items-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-all"
          >
            <IconCalendarPlus class="w-5 h-5 mr-2" /> Add Appointment
          </button>
          <button
            class="flex items-center bg-pink-600 text-white px-4 py-2 rounded-md hover:bg-pink-700 transition-all"
          >
            <IconPrescription class="w-5 h-5 mr-2" /> Add Prescription
          </button>
        </div>
      </div>

      <!-- Patient Info -->
      <div class="grid md:grid-cols-3 gap-6 bg-white p-6 rounded-xl shadow-sm">
        <div class="col-span-2 space-y-4">
          <h2 class="text-xl font-semibold text-gray-700">Personal Information</h2>

          <div class="grid grid-cols-2 gap-y-3">
            <p><strong>Registration ID:</strong> {{ patient.registration_id }}</p>
            <p><strong>Full Name:</strong> {{ fullName }}</p>
            <p><strong>Gender:</strong> {{ patient.gender }}</p>
            <p><strong>DOB:</strong> {{ patient.dob }}</p>
            <p><strong>Phone:</strong> {{ patient.phone }}</p>
            <p><strong>Email:</strong> {{ patient.email }}</p>
            <p><strong>Address:</strong> {{ patient.address }}</p>
            <p><strong>City:</strong> {{ patient.city }}</p>
            <p><strong>State:</strong> {{ patient.state }}</p>
            <p><strong>Country:</strong> {{ patient.country }}</p>
          </div>
        </div>

        <!-- Next of Kin -->
        <div class="space-y-6">
          <div class="bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-gray-700 font-semibold mb-2">Next of Kin</h3>
            <p><strong>Name:</strong> {{ patient.nok_name ?? '—' }}</p>
            <p><strong>Phone:</strong> {{ patient.nok_phone ?? '—' }}</p>
            <p><strong>Email:</strong> {{ patient.nok_email ?? '—' }}</p>
            <p><strong>Relationship:</strong> {{ patient.nok_relationship ?? '—' }}</p>
            <p><strong>Address:</strong> {{ patient.nok_address ?? '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Additional Information Section -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Additional Information</h2>

        <div class="prose max-w-none">
          <p>{{ patient.additional_details || 'No additional details available.' }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
