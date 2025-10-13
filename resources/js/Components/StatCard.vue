<template>
  <div :class="['p-6 rounded-xl shadow-md flex items-center justify-between bg-white dark:bg-gray-800']">
    <div>
      <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ title }}</p>
      <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ value }}</p>
    </div>
    <div :class="`p-3 rounded-full bg-${color}-100 text-${color}-600`">
      <component :is="iconComponent" :size="28" stroke-width="2.5" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  PillIcon,
  UsersIcon,
  UserIcon,
  DollarSignIcon as CurrencyIcon,
} from 'lucide-vue-next'

const props = defineProps({
  title: String,
  value: [String, Number],
  icon: String,
  color: String,
})

const iconComponent = computed(() => {
  switch (props.icon) {
    case 'Pill': return PillIcon
    case 'Users': return UsersIcon
    case 'User': return UserIcon
    case 'Currency': return CurrencyIcon
    default: return PillIcon
  }
})
</script>
