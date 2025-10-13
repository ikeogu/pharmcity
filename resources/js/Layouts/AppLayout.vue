<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden relative">
    <transition name="slide">
      <Sidebar
        v-if="!isMobile || isSidebarOpen"
        :isOpen="isSidebarOpen"
        @close="isSidebarOpen = false"
        class="z-40"
      />
    </transition>

    <div
      v-if="isMobile && isSidebarOpen"
      class="fixed inset-0 bg-black bg-opacity-40 z-30"
      @click="isSidebarOpen = false"
    ></div>

    <div
      class="flex-1 flex flex-col transition-all duration-300 overflow-hidden"
      :class="{
        'lg:ml-64': isSidebarOpen,
        'ml-0': !isSidebarOpen
      }"
    >
      <Navbar @toggle-sidebar="toggleSidebar" />

      <main
        class="flex-1 mt-16 overflow-y-auto overflow-x-hidden px-4 sm:px-6 lg:px-8 py-6"
      >
        <div class="w-full">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Sidebar from '@/Components/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'

const isSidebarOpen = ref(true)
const isMobile = ref(false)

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
  isSidebarOpen.value = !isMobile.value // open sidebar by default on desktop
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})
onBeforeUnmount(() => window.removeEventListener('resize', handleResize))
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
