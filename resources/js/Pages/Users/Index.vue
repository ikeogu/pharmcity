<template>
  <AppLayout>
    <!-- Success Flash -->
    <div
      v-if="$page.props.flash?.success"
      class="mb-4 p-3 rounded bg-green-100 text-green-700"
    >
      {{ $page.props.flash.success }}
    </div>

    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Manage Staff</h1>
        <p class="text-gray-500">User management overview</p>
      </div>

      <Link
        :href="route('users.create')"
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition"
      >
        Add Staff
      </Link>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full border-collapse">
        <thead class="bg-gray-100 text-left text-gray-600 text-sm uppercase">
          <tr>
            <th class="py-3 px-6">#</th>
            <th class="py-3 px-6">Name</th>
            <th class="py-3 px-6">Email</th>
            <th class="py-3 px-6">Role</th>
            <th class="py-3 px-6">Joined</th>
            <th class="py-3 px-6">Actions</th>

          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(user, index) in users.data"
            :key="user.id"
            class="border-b hover:bg-gray-50 text-gray-700"
          >
            <td class="py-3 px-6">{{ index + 1 }}</td>
            <td class="py-3 px-6">{{ user.first_name }} {{ user.last_name }}</td>
            <td class="py-3 px-6">{{ user.email }}</td>
            <td class="py-3 px-6">
              {{ Array.isArray(user.roles) ? user.roles.join(', ') : user.roles }}
            </td>
            <td class="py-3 px-6">{{ formatDate(user.created_at) }}</td>

            <td class="py-3 px-6 text-center flex justify-center space-x-3">
                <!-- View -->
                <Link
                :href="route('users.show', user.id)"
                class="text-blue-500 hover:text-blue-700 transition"
                title="View"
                >
                <EyeIcon class="w-5 h-5" />
                </Link>

                <!-- Edit -->
                <Link
                :href="route('users.edit', { user: user.id })"
                class="text-yellow-500 hover:text-yellow-700 transition"
                title="Edit"
                >
                <EditIcon class="w-5 h-5" />
                </Link>

                <!-- Delete -->
                <button
                @click="deleteUser(user.id)"
                class="text-red-500 hover:text-red-700 transition"
                title="Delete"
                >
                <Trash2Icon class="w-5 h-5" />
                </button>
                </td>

          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center items-center mt-6 space-x-2">
      <button
        v-for="(link, index) in filteredLinks"
        :key="index"
        @click.prevent="goTo(link.url)"
        :disabled="!link.url"
        class="px-4 py-2 text-sm rounded-md transition-all"
        :class="[
          link.active
            ? 'bg-indigo-600 text-white'
            : link.url
            ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            : 'bg-gray-100 text-gray-400 cursor-not-allowed',
        ]"
        v-html="link.label"
      ></button>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { PropType } from 'vue'
import { EyeIcon, EditIcon, Trash2Icon } from 'lucide-vue-next'

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface User {
  id: number | string
  first_name: string
  last_name: string
  email: string
  roles: string[] | string
  created_at: string
}

interface PaginatedUsers {
  data: User[]
  links: PaginationLink[]
}

const props = defineProps({
  users: {
    type: Object as PropType<PaginatedUsers>,
    required: true,
  },
})



const filteredLinks = computed(() =>
  props.users.meta.links.filter((link) => link.url)
)

function goTo(url: string) {
  if (!url) return
  router.visit(route('users.index', getQueryParams(url)))
}

function getQueryParams(url: string) {
  const query = new URL(url).searchParams
  return Object.fromEntries(query.entries())
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString()
}

function deleteUser(id: string) {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(route('users.destroy', id), {
      onSuccess: () => {
        // Optionally show a toast
        console.log('User deleted successfully')
      },
      onError: () => {
        console.error('Failed to delete user')
      },
    })
  }
}


</script>
