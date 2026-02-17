<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import { useOpsStore } from '@/stores/ops'
import KpiCard from '@/components/ui/KpiCard.vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend)

const opsStore = useOpsStore()
const loading = ref(true)

async function loadData() {
  loading.value = true
  try {
    if (opsStore.isAnnualView) {
      await opsStore.fetchYearData()
    } else {
      await Promise.all([
        opsStore.fetchDashboard(),
        opsStore.fetchDailyData()
      ])
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

watch(
  () => [opsStore.currentYear, opsStore.currentMonth, opsStore.viewMode],
  () => loadData()
)

function switchToMonthly(month) {
  opsStore.setMonth(opsStore.currentYear, month)
  opsStore.setViewMode('monthly')
}

function switchToAnnual() {
  opsStore.setViewMode('annual')
}

// Year selector
function prevYear() {
  opsStore.currentYear--
}

function nextYear() {
  opsStore.currentYear++
}

// Month navigation
function prevMonth() {
  opsStore.prevMonth()
}

function nextMonth() {
  opsStore.nextMonth()
}

// Chart data for yearly evolution
const yearChartData = computed(() => {
  const months = opsStore.yearData?.months
  if (!months) return { labels: [], datasets: [] }

  return {
    labels: monthLabels,
    datasets: [
      {
        label: 'Séances individuelles',
        data: Object.values(months).map(m => m.individual || 0),
        backgroundColor: '#10B981', // Emerald-500
        stack: 'main'
      },
      {
        label: 'Privatisations',
        data: Object.values(months).map(m => m.privatization || 0),
        backgroundColor: '#F59E0B', // Amber-500
        stack: 'main'
      },
      {
        label: 'Pack 2 séances',
        data: Object.values(months).map(m => m.pack_2 || 0),
        backgroundColor: '#3B82F6', // Blue-500
        stack: 'main'
      },
      {
        label: 'Pack 4 séances',
        data: Object.values(months).map(m => m.pack_4 || 0),
        backgroundColor: '#8B5CF6', // Violet-500
        stack: 'main'
      },
      {
        label: 'Dépenses HT',
        data: Object.values(months).map(m => -(m.expenses || 0)),
        backgroundColor: '#EF4444', // Red-500
        stack: 'main'
      }
    ]
  }
})

// Chart data for daily view
const dailyChartData = computed(() => {
  const days = opsStore.dailyData?.days
  if (!days) return { labels: [], datasets: [] }

  const dayLabels = Object.keys(days).map(d => d.toString())
  const dayValues = Object.values(days)

  return {
    labels: dayLabels,
    datasets: [
      {
        label: 'Séances individuelles',
        data: dayValues.map(d => d.individual || 0),
        backgroundColor: '#10B981', // Emerald-500
        stack: 'main'
      },
      {
        label: 'Privatisations',
        data: dayValues.map(d => d.privatization || 0),
        backgroundColor: '#F59E0B', // Amber-500
        stack: 'main'
      },
      {
        label: 'Pack 2 séances',
        data: dayValues.map(d => d.pack_2 || 0),
        backgroundColor: '#3B82F6', // Blue-500
        stack: 'main'
      },
      {
        label: 'Pack 4 séances',
        data: dayValues.map(d => d.pack_4 || 0),
        backgroundColor: '#8B5CF6', // Violet-500
        stack: 'main'
      },
      {
        label: 'Dépenses HT',
        data: dayValues.map(d => -(d.expenses || 0)),
        backgroundColor: '#EF4444', // Red-500
        stack: 'main'
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: { color: '#9CA3AF' }
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          const label = context.dataset.label || ''
          const value = Math.abs(context.parsed.y || 0)
          return `${label}: ${value.toLocaleString('fr-FR')} €`
        }
      }
    }
  },
  scales: {
    x: {
      stacked: true,
      ticks: { color: '#9CA3AF' },
      grid: { color: '#374151' }
    },
    y: {
      stacked: true,
      ticks: {
        color: '#9CA3AF',
        callback: (value) => Math.abs(value).toLocaleString('fr-FR') + ' €'
      },
      grid: { color: '#374151' }
    }
  }
}

const monthLabels = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec']
const monthLabelsFull = ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre']

function formatCurrency(value) {
  if (value === null || value === undefined) return '-'
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

// Donut chart options
const donutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          const value = context.parsed || 0
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percent = total > 0 ? Math.round((value / total) * 100) : 0
          return `${context.label}: ${value.toLocaleString('fr-FR')} € (${percent}%)`
        }
      }
    }
  }
}

// Helper to build donut chart data
function buildDonutData(data) {
  return {
    labels: data.map(d => d.label),
    datasets: [{
      data: data.map(d => d.value),
      backgroundColor: data.map(d => d.color),
      borderWidth: 0,
      hoverOffset: 4
    }]
  }
}

// Year donut charts
const yearClientTypeChart = computed(() => {
  const totals = opsStore.yearData?.totals
  if (!totals) return null
  return buildDonutData([
    { label: 'Particuliers', value: totals.individual || 0, color: '#10B981' },
    { label: 'Associations', value: totals.privatization || 0, color: '#F59E0B' }
  ])
})

const yearRevenueTypeChart = computed(() => {
  const totals = opsStore.yearData?.totals
  if (!totals) return null
  return buildDonutData([
    { label: 'Séances', value: totals.sessions || 0, color: '#10B981' },
    { label: 'Packs', value: totals.prepaid_packs || 0, color: '#3B82F6' }
  ])
})

const yearRevenueDetailChart = computed(() => {
  const totals = opsStore.yearData?.totals
  if (!totals) return null
  return buildDonutData([
    { label: 'Individuelles', value: totals.individual || 0, color: '#10B981' },
    { label: 'Privatisations', value: totals.privatization || 0, color: '#F59E0B' },
    { label: 'Pack 2', value: totals.pack_2 || 0, color: '#3B82F6' },
    { label: 'Pack 4', value: totals.pack_4 || 0, color: '#8B5CF6' }
  ])
})

// Month donut charts
const monthClientTypeChart = computed(() => {
  const totals = opsStore.dailyData?.totals
  if (!totals) return null
  return buildDonutData([
    { label: 'Particuliers', value: totals.individual || 0, color: '#10B981' },
    { label: 'Associations', value: totals.privatization || 0, color: '#F59E0B' }
  ])
})

const monthRevenueTypeChart = computed(() => {
  const totals = opsStore.dailyData?.totals
  if (!totals) return null
  return buildDonutData([
    { label: 'Séances', value: totals.sessions || 0, color: '#10B981' },
    { label: 'Packs', value: totals.prepaid_packs || 0, color: '#3B82F6' }
  ])
})

const monthRevenueDetailChart = computed(() => {
  const totals = opsStore.dailyData?.totals
  if (!totals) return null
  return buildDonutData([
    { label: 'Individuelles', value: totals.individual || 0, color: '#10B981' },
    { label: 'Privatisations', value: totals.privatization || 0, color: '#F59E0B' },
    { label: 'Pack 2', value: totals.pack_2 || 0, color: '#3B82F6' },
    { label: 'Pack 4', value: totals.pack_4 || 0, color: '#8B5CF6' }
  ])
})

// Get totals for center display
const yearTotals = computed(() => opsStore.yearData?.totals || {})
const monthTotals = computed(() => opsStore.dailyData?.totals || {})

// Expense category colors palette
const expenseColors = [
  '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16',
  '#22C55E', '#14B8A6', '#06B6D4', '#3B82F6', '#6366F1',
  '#8B5CF6', '#A855F7', '#D946EF', '#EC4899', '#F43F5E'
]

// Year expense chart
const yearExpenseChart = computed(() => {
  const categories = opsStore.yearData?.expenses_by_category
  if (!categories || categories.length === 0) return null

  return buildDonutData(categories.map((cat, idx) => ({
    label: cat.name,
    value: parseFloat(cat.total) || 0,
    color: cat.color || expenseColors[idx % expenseColors.length]
  })))
})

const topYearExpenseCategories = computed(() => {
  const categories = opsStore.yearData?.expenses_by_category
  if (!categories) return []
  return categories.slice(0, 5).map((cat, idx) => ({
    name: cat.name,
    total: parseFloat(cat.total) || 0,
    color: cat.color || expenseColors[idx % expenseColors.length]
  }))
})

// Month expense chart
const monthExpenseChart = computed(() => {
  const categories = opsStore.dailyData?.expenses_by_category
  if (!categories || categories.length === 0) return null

  return buildDonutData(categories.map((cat, idx) => ({
    label: cat.name,
    value: parseFloat(cat.total) || 0,
    color: cat.color || expenseColors[idx % expenseColors.length]
  })))
})

const topMonthExpenseCategories = computed(() => {
  const categories = opsStore.dailyData?.expenses_by_category
  if (!categories) return []
  return categories.slice(0, 5).map((cat, idx) => ({
    name: cat.name,
    total: parseFloat(cat.total) || 0,
    color: cat.color || expenseColors[idx % expenseColors.length]
  }))
})

</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-white">Dashboard</h1>

      <!-- View toggle & Period selector -->
      <div class="flex items-center gap-4">
        <!-- View mode toggle -->
        <div class="flex items-center bg-gray-800 rounded-lg p-1">
          <button
            @click="switchToAnnual"
            :class="[
              'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
              opsStore.isAnnualView
                ? 'bg-ops-600 text-white'
                : 'text-gray-400 hover:text-white'
            ]"
          >
            Annuel
          </button>
          <button
            @click="opsStore.setViewMode('monthly')"
            :class="[
              'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
              opsStore.isMonthlyView
                ? 'bg-ops-600 text-white'
                : 'text-gray-400 hover:text-white'
            ]"
          >
            Mensuel
          </button>
        </div>

        <!-- Year selector (annual view) -->
        <div v-if="opsStore.isAnnualView" class="flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-1.5">
          <button @click="prevYear" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="text-white font-medium min-w-[60px] text-center">{{ opsStore.currentYear }}</span>
          <button @click="nextYear" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Month selector (monthly view) -->
        <div v-else class="flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-1.5">
          <button @click="prevMonth" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="text-white font-medium min-w-[120px] text-center">
            {{ opsStore.monthLabel }} {{ opsStore.currentYear }}
          </span>
          <button @click="nextMonth" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Annual View -->
    <template v-else-if="opsStore.isAnnualView">
      <!-- KPIs -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <KpiCard
          title="CA annuel HT"
          :value="opsStore.yearData?.totals?.revenue"
          color="green"
        />
        <KpiCard
          title="Depenses annuelles HT"
          :value="opsStore.yearData?.totals?.expenses"
          color="red"
        />
        <KpiCard
          title="Resultat HT"
          :value="opsStore.yearData?.totals?.balance"
          :color="(opsStore.yearData?.totals?.balance || 0) >= 0 ? 'green' : 'red'"
        />
      </div>

      <!-- Revenue Breakdown - Donut Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Particuliers vs Associations -->
        <div class="card" v-if="yearClientTypeChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Type de client</h4>
          <div class="h-36 relative">
            <Doughnut :data="yearClientTypeChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-white">{{ formatCurrency(yearTotals.sessions || 0) }}</div>
                <div class="text-xs text-gray-500">séances</div>
              </div>
            </div>
          </div>
          <div class="flex justify-center gap-4 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Particuliers</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Assos</span>
          </div>
        </div>

        <!-- Séances vs Packs -->
        <div class="card" v-if="yearRevenueTypeChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Mode d'achat</h4>
          <div class="h-36 relative">
            <Doughnut :data="yearRevenueTypeChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-white">{{ formatCurrency(yearTotals.revenue || 0) }}</div>
                <div class="text-xs text-gray-500">total</div>
              </div>
            </div>
          </div>
          <div class="flex justify-center gap-4 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> À l'unité</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Packs</span>
          </div>
        </div>

        <!-- Détail revenus -->
        <div class="card" v-if="yearRevenueDetailChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Détail revenus</h4>
          <div class="h-36 relative">
            <Doughnut :data="yearRevenueDetailChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-green-400">{{ formatCurrency(yearTotals.revenue || 0) }}</div>
                <div class="text-xs text-gray-500">CA</div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-1 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Indiv.</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Privat.</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Pack 2</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-violet-500"></span> Pack 4</span>
          </div>
        </div>

        <!-- Dépenses par catégorie -->
        <div class="card" v-if="yearExpenseChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Dépenses</h4>
          <div class="h-36 relative">
            <Doughnut :data="yearExpenseChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-red-400">{{ formatCurrency(yearTotals.expenses || 0) }}</div>
                <div class="text-xs text-gray-500">total</div>
              </div>
            </div>
          </div>
          <div class="mt-2 space-y-0.5 text-xs max-h-16 overflow-y-auto">
            <div v-for="(cat, idx) in topYearExpenseCategories" :key="idx" class="flex items-center justify-between">
              <span class="flex items-center gap-1 text-gray-400 truncate">
                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></span>
                {{ cat.name }}
              </span>
              <span class="text-gray-300 ml-2">{{ formatCurrency(cat.total) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Yearly Chart -->
      <div class="card mb-8">
        <h3 class="text-lg font-semibold text-white mb-4">Evolution {{ opsStore.currentYear }}</h3>
        <div class="h-72">
          <Bar
            v-if="yearChartData.labels.length"
            :data="yearChartData"
            :options="chartOptions"
          />
        </div>
      </div>

      <!-- Monthly breakdown table -->
      <div class="card overflow-hidden p-0">
        <table>
          <thead>
            <tr>
              <th>Mois</th>
              <th class="text-right">CA HT</th>
              <th class="text-right">Depenses HT</th>
              <th class="text-right">Resultat</th>
              <th class="w-20"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(data, month) in opsStore.yearData?.months" :key="month">
              <td class="font-medium">{{ monthLabelsFull[month - 1] }}</td>
              <td class="text-right text-green-400">{{ formatCurrency(data.revenue) }}</td>
              <td class="text-right text-red-400">{{ formatCurrency(data.expenses) }}</td>
              <td :class="['text-right font-medium', data.balance >= 0 ? 'text-green-400' : 'text-red-400']">
                {{ formatCurrency(data.balance) }}
              </td>
              <td>
                <button
                  @click="switchToMonthly(month)"
                  class="text-ops-400 hover:text-ops-300 text-sm"
                >
                  Voir
                </button>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-gray-800/50 font-bold">
              <td>Total</td>
              <td class="text-right text-green-400">{{ formatCurrency(opsStore.yearData?.totals?.revenue) }}</td>
              <td class="text-right text-red-400">{{ formatCurrency(opsStore.yearData?.totals?.expenses) }}</td>
              <td :class="['text-right', (opsStore.yearData?.totals?.balance || 0) >= 0 ? 'text-green-400' : 'text-red-400']">
                {{ formatCurrency(opsStore.yearData?.totals?.balance) }}
              </td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </template>

    <!-- Monthly View -->
    <template v-else>
      <!-- Back to annual -->
      <button
        @click="switchToAnnual"
        class="flex items-center gap-2 text-gray-400 hover:text-white mb-4"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Retour a la vue annuelle
      </button>

      <!-- KPIs -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <KpiCard
          title="CA du mois HT"
          :value="opsStore.dailyData?.totals?.revenue"
          color="green"
        />
        <KpiCard
          title="Depenses du mois HT"
          :value="opsStore.dailyData?.totals?.expenses"
          color="red"
        />
        <KpiCard
          title="Resultat du mois"
          :value="opsStore.dailyData?.totals?.balance"
          :color="(opsStore.dailyData?.totals?.balance || 0) >= 0 ? 'green' : 'red'"
        />
      </div>

      <!-- Revenue Breakdown - Donut Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Particuliers vs Associations -->
        <div class="card" v-if="monthClientTypeChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Type de client</h4>
          <div class="h-36 relative">
            <Doughnut :data="monthClientTypeChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-white">{{ formatCurrency(monthTotals.sessions || 0) }}</div>
                <div class="text-xs text-gray-500">séances</div>
              </div>
            </div>
          </div>
          <div class="flex justify-center gap-4 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Particuliers</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Assos</span>
          </div>
        </div>

        <!-- Séances vs Packs -->
        <div class="card" v-if="monthRevenueTypeChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Mode d'achat</h4>
          <div class="h-36 relative">
            <Doughnut :data="monthRevenueTypeChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-white">{{ formatCurrency(monthTotals.revenue || 0) }}</div>
                <div class="text-xs text-gray-500">total</div>
              </div>
            </div>
          </div>
          <div class="flex justify-center gap-4 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> À l'unité</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Packs</span>
          </div>
        </div>

        <!-- Détail revenus -->
        <div class="card" v-if="monthRevenueDetailChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Détail revenus</h4>
          <div class="h-36 relative">
            <Doughnut :data="monthRevenueDetailChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-green-400">{{ formatCurrency(monthTotals.revenue || 0) }}</div>
                <div class="text-xs text-gray-500">CA</div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-1 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Indiv.</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Privat.</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Pack 2</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-violet-500"></span> Pack 4</span>
          </div>
        </div>

        <!-- Dépenses par catégorie -->
        <div class="card" v-if="monthExpenseChart">
          <h4 class="text-sm font-medium text-gray-400 mb-2 text-center">Dépenses</h4>
          <div class="h-36 relative">
            <Doughnut :data="monthExpenseChart" :options="donutOptions" />
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div class="text-center">
                <div class="text-lg font-bold text-red-400">{{ formatCurrency(monthTotals.expenses || 0) }}</div>
                <div class="text-xs text-gray-500">total</div>
              </div>
            </div>
          </div>
          <div class="mt-2 space-y-0.5 text-xs max-h-16 overflow-y-auto">
            <div v-for="(cat, idx) in topMonthExpenseCategories" :key="idx" class="flex items-center justify-between">
              <span class="flex items-center gap-1 text-gray-400 truncate">
                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></span>
                {{ cat.name }}
              </span>
              <span class="text-gray-300 ml-2">{{ formatCurrency(cat.total) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Chart -->
      <div class="card mb-8">
        <h3 class="text-lg font-semibold text-white mb-4">
          {{ opsStore.monthLabel }} {{ opsStore.currentYear }} - Jour par jour
        </h3>
        <div class="h-72">
          <Bar
            v-if="dailyChartData.labels.length"
            :data="dailyChartData"
            :options="chartOptions"
          />
        </div>
      </div>

      <!-- Daily breakdown table -->
      <div class="card overflow-hidden p-0">
        <table>
          <thead>
            <tr>
              <th>Jour</th>
              <th class="text-right">CA HT</th>
              <th class="text-right">Depenses HT</th>
              <th class="text-right">Resultat</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(data, day) in opsStore.dailyData?.days" :key="day">
              <tr v-if="data.revenue > 0 || data.expenses > 0">
                <td class="font-medium">{{ day }}</td>
                <td class="text-right text-green-400">{{ data.revenue > 0 ? formatCurrency(data.revenue) : '-' }}</td>
                <td class="text-right text-red-400">{{ data.expenses > 0 ? formatCurrency(data.expenses) : '-' }}</td>
                <td :class="['text-right font-medium', data.balance >= 0 ? 'text-green-400' : 'text-red-400']">
                  {{ formatCurrency(data.balance) }}
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot>
            <tr class="bg-gray-800/50 font-bold">
              <td>Total</td>
              <td class="text-right text-green-400">{{ formatCurrency(opsStore.dailyData?.totals?.revenue) }}</td>
              <td class="text-right text-red-400">{{ formatCurrency(opsStore.dailyData?.totals?.expenses) }}</td>
              <td :class="['text-right', (opsStore.dailyData?.totals?.balance || 0) >= 0 ? 'text-green-400' : 'text-red-400']">
                {{ formatCurrency(opsStore.dailyData?.totals?.balance) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </template>
  </div>
</template>
