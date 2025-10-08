<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar v-if="!isMobile || sidebarOpen" class="z-30" />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col" :class="{ 'ml-64': !isMobile }">
      <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <main class="mt-16 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Sidebar from '@/Components/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'

const sidebarOpen = ref(false)
const isMobile = ref(false)

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})
onBeforeUnmount(() => window.removeEventListener('resize', handleResize))
</script>
