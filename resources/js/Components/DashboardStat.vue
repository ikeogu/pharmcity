<template>
  <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md flex items-center justify-between">
    <div>
      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
        {{ title }}
      </p>
      <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">
        {{ value }}
      </p>
    </div>
    <div :class="`bg-${color}-100 dark:bg-${color}-900/40 text-${color}-600 dark:text-${color}-400 rounded-full p-3`">
      <component :is="iconComponent" :size="28" stroke-width="2.5" />
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  UsersIcon,
  UserCheckIcon,
  AlertTriangleIcon,
  CurrencyIcon
} from 'lucide-vue-next'

const props = defineProps({
  title: String,
  value: [String, Number],
  icon: String,
  color: String
})

const iconMap: Record<string, any> = {
  users: UsersIcon,
  userCheck: UserCheckIcon,
  alert: AlertTriangleIcon,
  currency: CurrencyIcon
}

const iconComponent = iconMap[props.icon] || UsersIcon
</script>
