<template>
  <aside
    :class="[
      'fixed left-0 top-0 z-40 h-screen w-64 transition-transform duration-300',
      props.isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <div class="flex h-full flex-col overflow-y-auto border-r border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
      <!-- Logo -->
      <div class="flex h-16 items-center justify-between px-6 border-b border-gray-200 dark:border-gray-700">
        <Link :href="route('dashboard')" class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
            <span class="text-lg font-bold text-white">P</span>
          </div>
          <span class="text-xl font-bold text-gray-900 dark:text-white">PharmCity</span>
        </Link>

        <button
          @click="emit('close')"
          class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
        <div class="mb-2">
          <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            Menu
          </p>
        </div>

        <!-- Dashboard -->
        <Link :href="route('dashboard')" :class="getNavLinkClass('dashboard')">
             <IconHome /> Dashboard
        </Link>

        <!-- Manage Staff -->
        <Link v-if="hasRole(['superadmin'])" :href="route('users.index')" :class="getNavLinkClass('users.index')">
            <IconUsers /> Manage Staff
        </Link>

        <!-- Drugs -->
        <Link v-if="hasRole(['superadmin','pharmacist'])" :href="route('drugs.index')" :class="getNavLinkClass('drugs.index')">
            <IconPill /> Drugs
        </Link>

         <Link v-if="hasRole(['superadmin','reporter'])" :href="route('patients.index')" :class="getNavLinkClass('patients.index')">
            <IconUsers /> Patients
        </Link>

         <Link v-if="hasRole(['superadmin','sales'])" :href="route('pos.index')" :class="getNavLinkClass('pos.index')">
            <PosIcon /> POS
        </Link>

      </nav>

      <!-- User Profile -->
      <div class="border-t border-gray-200 p-4 dark:border-gray-700">
        <div v-if="user" class="flex items-center gap-3">
          <img
            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.first_name)}&background=0D8ABC&color=fff`"
            :alt="user.first_name"
            class="h-10 w-10 rounded-full"
          />
          <div class="flex-1 min-w-0">
            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ user.first_name }}</p>
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
          </div>
          <button @click="handleLogout" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Logout">
            <IconLogout />
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

// Example icons (use lucide-vue-next or heroicons)
import {
  HomeIcon as IconHome,
  UsersIcon as IconUsers,
  UserIcon as IconUser,
  PillIcon as IconPill,
  ClipboardListIcon as IconClipboard,
  LogOutIcon as IconLogout,
  UserPlusIcon as IconPeople,
  Calculator as PosIcon
} from 'lucide-vue-next';

interface Props {
  isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user);

// Helper function with proper typing
function hasRole(roles: string[]): boolean {
  if (!user.value?.roles) return false;

  const userRoles = Array.isArray(user.value.roles)
    ? user.value.roles.map(r => (typeof r === 'string' ? r : r.name))
    : [user.value.roles];

  const requiredRoles = Array.isArray(roles) ? roles : [roles];

  // Return true if user has *any* of the roles
  return requiredRoles.some(role => userRoles.includes(role));
}

// Example of permission checker (optional)
function hasPermission(permission: string): boolean {
  if (!user.value?.permissions) return false;
  return user.value.permissions.some(p => p.name === permission);
}

const getNavLinkClass = (name: string) => [
  'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
  route().current(name)
    ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
    : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
];

const handleLogout = () => {
  router.post(route('logout'));
};
</script>
