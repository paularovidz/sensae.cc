<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { sessionsApi } from '@/services/api'
import SessionCalendar from '@/components/ui/SessionCalendar.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import {
  Chart as ChartJS,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Doughnut, Bar } from 'vue-chartjs'

ChartJS.register(
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  personId: {
    type: String,
    required: true
  }
})

const loading = ref(true)
const stats = ref(null)
const calendarYear = ref(new Date().getFullYear())
const calendarMonth = ref(new Date().getMonth() + 1)

const sessionEndLabels = {
  accepts: 'Accepte',
  refuses: 'Refuse',
  interrupts: 'Interrompt'
}

const behaviorLabels = {
  calm: 'Calme',
  agitated: 'Agité',
  tired: 'Fatigué',
  defensive: 'Défensif',
  anxious: 'Inquiet',
  passive: 'Passif'
}

const communicationLabels = {
  body: 'Corporelle',
  verbal: 'Verbale',
  vocal: 'Vocale'
}

const sensoryTypeLabels = {
  tactile: 'Tactile',
  visual: 'Visuelle',
  olfactory: 'Olfactive',
  gustatory: 'Gustative',
  auditory: 'Auditive',
  proprioceptive: 'Proprioceptive',
  vestibular: 'Vestibulaire'
}

onMounted(async () => {
  await loadStats()
})

async function loadStats() {
  loading.value = true
  try {
    const response = await sessionsApi.getPersonStats(props.personId)
    stats.value = response.data.data
  } catch (e) {
    console.error('Error loading stats:', e)
  } finally {
    loading.value = false
  }
}

function handleMonthChange({ year, month }) {
  calendarYear.value = year
  calendarMonth.value = month
}

// Session end chart data
const sessionEndChartData = computed(() => {
  if (!stats.value?.session_end_distribution) return null

  const distribution = stats.value.session_end_distribution
  const labels = distribution.map(d => sessionEndLabels[d.session_end] || d.session_end)
  const data = distribution.map(d => parseInt(d.count))

  // accepts = vert, refuses = orange, interrupts = rouge
  const colors = {
    accepts: '#10B981',
    refuses: '#F59E0B',
    interrupts: '#EF4444'
  }

  return {
    labels,
    datasets: [{
      data,
      backgroundColor: distribution.map(d => colors[d.session_end] || '#6B7280'),
      borderWidth: 0
    }]
  }
})

// Behavior end chart data
const behaviorEndChartData = computed(() => {
  if (!stats.value?.behavior_end_distribution) return null

  const distribution = stats.value.behavior_end_distribution
  const labels = distribution.map(d => behaviorLabels[d.behavior_end] || d.behavior_end)
  const data = distribution.map(d => parseInt(d.count))

  const colors = {
    calm: '#10B981',
    agitated: '#F59E0B',
    tired: '#6B7280',
    defensive: '#EF4444',
    anxious: '#F97316',
    passive: '#9CA3AF'
  }

  return {
    labels,
    datasets: [{
      label: 'Comportement en fin de séance',
      data,
      backgroundColor: distribution.map(d => colors[d.behavior_end] || '#6B7280')
    }]
  }
})

// Communication chart data
const communicationChartData = computed(() => {
  if (!stats.value?.communication_distribution) return null

  const distribution = stats.value.communication_distribution
  const labels = Object.keys(distribution).map(k => communicationLabels[k] || k)
  const data = Object.values(distribution)

  return {
    labels,
    datasets: [{
      label: 'Types de communication',
      data,
      backgroundColor: ['#818CF8', '#6366F1', '#4F46E5']
    }]
  }
})

// Sensory appreciation bar chart data (horizontal bars with positive/negative only)
const sensoryBarData = computed(() => {
  if (!stats.value?.sensory_appreciation || stats.value.sensory_appreciation.length === 0) return null

  const appreciation = stats.value.sensory_appreciation
  const labels = appreciation.map(a => sensoryTypeLabels[a.type] || a.type)

  // Negative values go left, positive go right
  return {
    labels,
    datasets: [
      {
        label: 'Négatif',
        data: appreciation.map(a => -(parseInt(a.negative) || 0)),
        backgroundColor: '#EF4444',
        borderRadius: 4
      },
      {
        label: 'Positif',
        data: appreciation.map(a => parseInt(a.positive) || 0),
        backgroundColor: '#10B981',
        borderRadius: 4
      }
    ]
  }
})

// Dark mode chart options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: '#9CA3AF'
      }
    }
  }
}

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1,
        color: '#9CA3AF'
      },
      grid: {
        color: '#374151'
      }
    },
    x: {
      ticks: {
        color: '#9CA3AF'
      },
      grid: {
        color: '#374151'
      }
    }
  }
}

const sensoryBarOptions = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    x: {
      stacked: true,
      ticks: {
        callback: (value) => Math.abs(value),
        color: '#9CA3AF'
      },
      grid: {
        color: (context) => context.tick.value === 0 ? '#6B7280' : '#374151'
      }
    },
    y: {
      stacked: true,
      ticks: {
        color: '#9CA3AF'
      },
      grid: {
        color: '#374151'
      }
    }
  },
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: '#9CA3AF'
      }
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          const value = Math.abs(context.raw)
          return `${context.dataset.label}: ${value}`
        }
      }
    }
  }
}
</script>

<template>
  <div class="space-y-6">
    <LoadingSpinner v-if="loading" size="lg" class="py-12" />

    <template v-else-if="stats">
      <!-- 3 items per row: Calendar + charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <!-- Calendar -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
          <h3 class="text-base font-semibold text-white mb-3">Calendrier des séances</h3>
          <SessionCalendar
            :data="stats.session_dates"
            :year="calendarYear"
            :month="calendarMonth"
            @change-month="handleMonthChange"
          />
        </div>

        <!-- Session end distribution -->
        <div v-if="sessionEndChartData" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
          <h3 class="text-base font-semibold text-white mb-3">Fin de séance</h3>
          <div class="h-48">
            <Doughnut :data="sessionEndChartData" :options="chartOptions" />
          </div>
        </div>

        <!-- Behavior end -->
        <div v-if="behaviorEndChartData" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
          <h3 class="text-base font-semibold text-white mb-3">Comportement fin de séance</h3>
          <div class="h-48">
            <Bar :data="behaviorEndChartData" :options="barChartOptions" />
          </div>
        </div>

        <!-- Communication -->
        <div v-if="communicationChartData" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
          <h3 class="text-base font-semibold text-white mb-3">Types de communication</h3>
          <div class="h-48">
            <Bar :data="communicationChartData" :options="barChartOptions" />
          </div>
        </div>
      </div>

      <!-- Sensory preferences bar chart - full width -->
      <div v-if="sensoryBarData" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
        <h3 class="text-base font-semibold text-white mb-2">Préférences sensorielles</h3>
        <p class="text-xs text-gray-400 mb-3">Positif (vert), Négatif (rouge)</p>
        <div class="h-64">
          <Bar :data="sensoryBarData" :options="sensoryBarOptions" />
        </div>
      </div>

      <!-- No data message -->
      <div v-if="!sessionEndChartData && !behaviorEndChartData && !communicationChartData && !sensoryBarData" class="bg-gray-800 rounded-xl border border-gray-700 p-6 text-center text-gray-400">
        Pas assez de données pour afficher les statistiques. Créez plus de séances pour voir les analyses.
      </div>
    </template>
  </div>
</template>
