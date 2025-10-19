<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
          Welcome back, {{ user.first_name }}!
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">
          Here's today's pharmacy overview.
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard title="Total Drugs" :value="stats.totalDrugs" icon="Pill" color="blue" />
        <StatCard title="Total Patients" :value="stats.totalPatients" icon="Users" color="green" />
        <StatCard title="Total Users" :value="stats.totalUsers" icon="User" color="indigo" />
        <StatCard title="Total Sales" :value="formatCurrency(stats.totalSales)" icon="Currency" color="orange" />
      </div>

      <!-- Charts + Recent Sales -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Weekly Sales Chart -->
        <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Weekly Sales Trend</h3>
          <div class="h-80">
            <Bar :data="chartData" :options="chartOptions" />
          </div>
        </div>

        <!-- Recent Sales -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Recent Sales</h3>
          <div v-if="recentSales.length" class="space-y-4">
            <div v-for="sale in recentSales" :key="sale.id" class="flex justify-between">
              <div @click="router.visit(`/pos/sales/${sale.id}`)" class="cursor-pointer">
                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ sale.invoice_number }}</p>
              </div>

              <div class="text-right">
                <p class="font-bold text-gray-900 dark:text-white">₦{{ Number(sale.total) }}</p>
                <span :class="getStatusClass(sale.status)">{{ sale.status }}</span>
              </div>
            </div>
          </div>
          <p v-else class="text-center text-gray-500 dark:text-gray-400 py-8">No recent sales found.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'

import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import StatCard from '@/Components/StatCard.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { PageProps, Stats, Sale } from '@/types'
import { usePage, router } from '@inertiajs/vue3'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

interface WeeklySale {
  day: string
  total: string
}

interface DashboardPageProps extends PageProps {
  dashboardData?: Stats
  recentSales: Sale[]
  weeklySales: WeeklySale[]
}

const page = usePage<DashboardPageProps>()

const user = computed(() => page.props.auth.user)

const stats = computed<Stats>(() => page.props.dashboardData || {
  totalDrugs: 0,
  totalPatients: 0,
  totalUsers: 0,
  totalSales: 0,
})

const recentSales = computed<Sale[]>(() => page.props.recentSales || [])
const weeklySales = computed<WeeklySale[]>(() => page.props.weeklySales || [])

// Convert Laravel data for Chart.js
const chartData = computed(() => {
  const labels = weeklySales.value.map((item: WeeklySale) => item.day)
  const totals = weeklySales.value.map((item: WeeklySale) => parseFloat(item.total))

  return {
    labels,
    datasets: [
      {
        label: 'Sales (₦)',
        backgroundColor: '#3b82f6',
        borderRadius: 5,
        data: totals,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true } },
}

const getStatusClass = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid': return 'text-xs font-medium bg-green-100 text-green-700 px-2 py-0.5 rounded-full'
    case 'pending': return 'text-xs font-medium bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full'
    default: return 'text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full'
  }
}

const formatCurrency = (amount: number | string) => {
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount
  return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(numAmount)
}
</script>
