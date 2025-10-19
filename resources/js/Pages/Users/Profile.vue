<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto bg-white shadow rounded-lg p-6">
      <!-- Header Section -->
      <div class="flex justify-between items-center border-b pb-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">
            {{ user.title }} {{ user.first_name }} {{ user.last_name }}
          </h1>
          <p class="text-gray-500">@{{ user.username }}</p>
        </div>
        <Link
          :href="route('users.index')"
          class="bg-gray-100 text-gray-700 px-3 py-2 rounded-md hover:bg-gray-200 transition"
        >
          ← Back
        </Link>
      </div>

      <!-- Profile Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3">
          <ProfileField label="Email" :value="user.email" />
          <ProfileField label="Phone" :value="user.phone ?? ''" />
          <ProfileField label="Gender" :value="user.gender ?? ''" />
          <ProfileField label="Date of Birth" :value="user.dob ?? ''" />
        </div>

        <div class="space-y-3">
          <ProfileField label="Country" :value="user.country ?? ''" />
          <ProfileField label="State" :value="user.state ?? ''" />
          <ProfileField label="City" :value="user.city ?? ''" />
          <ProfileField label="Address" :value="user.address ?? ''" />
        </div>
      </div>

      <!-- Roles -->
      <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Roles</h2>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="role in userRoles"
            :key="role"
            class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm"
          >
            {{ role }}
          </span>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 border-t pt-4 text-sm text-gray-500 flex justify-between">
        <p>Joined on {{ user.created_at }}</p>

        <div class="flex gap-3">
          <button
            @click="deleteUser(user.id)"
            class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 flex items-center gap-1"
          >
            <Trash2Icon class="w-4 h-4" /> Delete
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { EditIcon, Trash2Icon } from 'lucide-vue-next'
import { computed } from 'vue'
import type { User } from '@/types'

interface UserWrapper {
  data: User
}

// Type guard to check if user has data property
function isUserWrapper(user: User | UserWrapper): user is UserWrapper {
  return 'data' in user && user.data !== undefined
}

const props = defineProps<{
  user: User | UserWrapper
}>()

const user = computed<User>(() => {
  return isUserWrapper(props.user) ? props.user.data : props.user
})

const userRoles = computed(() => {
  if (!user.value.roles) return []
  return user.value.roles.map(role => typeof role === 'string' ? role : role.name)
})

function deleteUser(id: number | string) {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(route('users.destroy', { user: id }), {
      onSuccess: () => alert('User deleted successfully'),
    })
  }
}
</script>

<!-- ✅ Reusable small field component -->
<script lang="ts">
export default {
  components: {
    ProfileField: {
      props: {
        label: String,
        value: String,
      },
      template: `
        <div>
          <p class="text-gray-500 text-sm">{{ label }}</p>
          <p class="font-medium text-gray-800">{{ value || '-' }}</p>
        </div>
      `,
    },
  },
}
</script>
