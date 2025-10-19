<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import type { PropType } from 'vue'
import { EyeIcon, EditIcon, Trash2Icon } from 'lucide-vue-next'
import StaffSearch from '@/Components/StaffSearch.vue'
import type { PageProps } from '@/types'

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
  meta: { links: PaginationLink[] }
}

const props = defineProps({
  users: {
    type: Object as PropType<PaginatedUsers>,
    required: true,
  },
})

const page = usePage<PageProps>()

// ✅ local reactive copy of users for live search
const filteredUsers = ref<User[]>([...props.users.data])

const updateSearchResults = (results: User[]) => {
  if (results.length > 0) {
    filteredUsers.value = results
  } else {
    // fallback to original pagination data if no search
    filteredUsers.value = [...props.users.data]
  }
}

const filteredLinks = computed(() =>
  props.users.meta.links.filter((link) => link.url)
)

function goTo(url: string | null) {
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

function deleteUser(id: number | string) {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(route('users.destroy', { user: id }), {
      onSuccess: () => console.log('User deleted successfully'),
      onError: () => console.error('Failed to delete user'),
    })
  }
}
</script>

<template>
  <AppLayout>
    <div
      v-if="page.props.flash?.success"
      class="mb-4 p-3 rounded bg-green-100 text-green-700"
    >
      {{ page.props.flash.success }}
    </div>

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

    <!-- Staff Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-lg font-semibold mb-4">Search Staff</h2>
      <StaffSearch @update-results="updateSearchResults" />
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
            v-for="(user, index) in filteredUsers"
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
              <Link
                :href="route('users.show', user.id)"
                class="text-blue-500 hover:text-blue-700 transition"
                title="View"
              >
                <EyeIcon class="w-5 h-5" />
              </Link>

              <Link
                :href="route('users.edit', { user: user.id })"
                class="text-yellow-500 hover:text-yellow-700 transition"
                title="Edit"
              >
                <EditIcon class="w-5 h-5" />
              </Link>

              <button
                @click="deleteUser(user.id)"
                class="text-red-500 hover:text-red-700 transition"
                title="Delete"
              >
                <Trash2Icon class="w-5 h-5" />
              </button>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="filteredUsers.length === 0">
            <td colspan="6" class="text-center py-6 text-gray-500">No staff found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="filteredUsers.length === props.users.data.length" class="flex justify-center items-center mt-6 space-x-2">
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
