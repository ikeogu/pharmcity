<template>
  <header
    class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 fixed left-64 right-0 top-0 z-20"
  >
    <div class="flex items-center gap-4">
      <button class="lg:hidden text-gray-600 hover:text-gray-900" @click="$emit('toggle-sidebar')">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <input
        type="text"
        placeholder="Search..."
        class="hidden md:block px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-400 outline-none"
      />
    </div>

    <div class="flex items-center gap-4">
      <button class="text-gray-600 hover:text-gray-900">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405M15 17a3 3 0 11-6 0m6 0a3 3 0 11-6 0M15 7h.01M9 7h.01" />
        </svg>
      </button>

      <img
        src="https://ui-avatars.com/api/?name=User&background=4F46E5&color=fff"
        alt="User Avatar"
        class="w-8 h-8 rounded-full"
      />
    </div>
  </header>
</template>

<script setup lang="ts"></script>
