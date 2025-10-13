<script setup>
import { ref, watch } from 'vue'
import { debounce } from 'lodash'

const emit = defineEmits(['update-results'])

const searchQuery = ref('')
const loading = ref(false)

const searchStaff = debounce(async () => {
  if (searchQuery.value.length < 2) {
    emit('update-results', []) // clear results if empty
    return
  }

  loading.value = true

  try {
    const response = await axios.get('/staffs/search', {
      params: { query: searchQuery.value },
    })
    emit('update-results', response.data.staff)
  } catch (error) {
    console.error('Staff search failed:', error)
    emit('update-results', [])
  } finally {
    loading.value = false
  }
}, 300)

watch(searchQuery, () => {
  searchStaff()
})
</script>

<template>
  <div class="relative mb-6">
    <input
      v-model="searchQuery"
      type="text"
      placeholder="Search staff by name, email, or role..."
      class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    />

    <svg
      v-if="loading"
      class="animate-spin h-5 w-5 text-gray-400 absolute right-3 top-4"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
  </div>
</template>
