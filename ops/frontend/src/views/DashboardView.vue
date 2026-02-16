<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import { useOpsStore } from '@/stores/ops'
import KpiCard from '@/components/ui/KpiCard.vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

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
        label: 'Séances',
        data: Object.values(months).map(m => m.sessions || 0),
        backgroundColor: '#10B981', // Emerald-500
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
        label: 'Séances',
        data: dayValues.map(d => d.sessions || 0),
        backgroundColor: '#10B981', // Emerald-500
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

// Revenue subtitles showing breakdown (sessions + prepaid packs by type)
const yearRevenueSubtitle = computed(() => {
  const totals = opsStore.yearData?.totals
  if (!totals) return ''
  const parts = []
  if (totals.sessions > 0) {
    parts.push(`Séances: ${formatCurrency(totals.sessions)}`)
  }
  if (totals.pack_2 > 0) {
    parts.push(`Pack 2 séances: ${formatCurrency(totals.pack_2)}`)
  }
  if (totals.pack_4 > 0) {
    parts.push(`Pack 4 séances: ${formatCurrency(totals.pack_4)}`)
  }
  return parts.join(' | ')
})

const monthRevenueSubtitle = computed(() => {
  const totals = opsStore.dailyData?.totals
  const kpis = opsStore.dashboard?.kpis?.revenue
  if (!totals && !kpis) return ''

  const parts = []
  const sessionCount = kpis?.session_count || 0
  const sessions = totals?.sessions || kpis?.sessions || 0
  const pack2 = totals?.pack_2 || kpis?.pack_2 || 0
  const pack4 = totals?.pack_4 || kpis?.pack_4 || 0

  if (sessions > 0 || sessionCount > 0) {
    parts.push(`${sessionCount} séances (${formatCurrency(sessions)})`)
  }
  if (pack2 > 0) {
    const pack2Count = kpis?.pack_2_count || 0
    parts.push(`${pack2Count} pack 2 séances (${formatCurrency(pack2)})`)
  }
  if (pack4 > 0) {
    const pack4Count = kpis?.pack_4_count || 0
    parts.push(`${pack4Count} pack 4 séances (${formatCurrency(pack4)})`)
  }
  return parts.join(' | ')
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
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <KpiCard
          title="CA annuel HT"
          :value="opsStore.yearData?.totals?.revenue"
          :subtitle="yearRevenueSubtitle"
          color="green"
        />
        <KpiCard
          title="Depenses annuelles HT"
          :value="opsStore.yearData?.totals?.expenses"
          subtitle=""
          color="red"
        />
        <KpiCard
          title="Resultat HT"
          :value="opsStore.yearData?.totals?.balance"
          subtitle=""
          :color="(opsStore.yearData?.totals?.balance || 0) >= 0 ? 'green' : 'red'"
        />
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
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <KpiCard
          title="CA du mois HT"
          :value="opsStore.dailyData?.totals?.revenue"
          :subtitle="monthRevenueSubtitle"
          color="green"
        />
        <KpiCard
          title="Depenses du mois HT"
          :value="opsStore.dailyData?.totals?.expenses"
          subtitle=""
          color="red"
        />
        <KpiCard
          title="Resultat du mois"
          :value="opsStore.dailyData?.totals?.balance"
          subtitle=""
          :color="(opsStore.dailyData?.totals?.balance || 0) >= 0 ? 'green' : 'red'"
        />
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
