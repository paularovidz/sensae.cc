<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  },
  year: {
    type: Number,
    default: () => new Date().getFullYear()
  },
  month: {
    type: Number,
    default: () => new Date().getMonth() + 1
  }
})

const emit = defineEmits(['change-month'])

const months = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
]

const days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']

const calendarDays = computed(() => {
  const year = props.year
  const month = props.month - 1 // JS months are 0-indexed

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)

  // Get the day of week for first day (0 = Sunday, we want Monday = 0)
  let startDayOfWeek = firstDay.getDay() - 1
  if (startDayOfWeek < 0) startDayOfWeek = 6

  const daysInMonth = lastDay.getDate()
  const result = []

  // Add empty cells for days before the first of the month
  for (let i = 0; i < startDayOfWeek; i++) {
    result.push({ day: null, count: 0 })
  }

  // Add days of the month
  for (let day = 1; day <= daysInMonth; day++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    const count = props.data[dateStr] || 0
    result.push({ day, count, date: dateStr })
  }

  return result
})

const today = computed(() => {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
})

function getIntensityClass(count) {
  if (count === 0) return 'bg-gray-50'
  if (count === 1) return 'bg-gray-200'
  if (count <= 3) return 'bg-primary-200'
  if (count <= 5) return 'bg-primary-400'
  return 'bg-primary-600 text-white'
}

function prevMonth() {
  let newMonth = props.month - 1
  let newYear = props.year
  if (newMonth < 1) {
    newMonth = 12
    newYear--
  }
  emit('change-month', { year: newYear, month: newMonth })
}

function nextMonth() {
  let newMonth = props.month + 1
  let newYear = props.year
  if (newMonth > 12) {
    newMonth = 1
    newYear++
  }
  emit('change-month', { year: newYear, month: newMonth })
}
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-100 p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <button @click="prevMonth" class="p-1 hover:bg-gray-100 rounded">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h3 class="font-semibold text-gray-900">{{ months[month - 1] }} {{ year }}</h3>
      <button @click="nextMonth" class="p-1 hover:bg-gray-100 rounded">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <!-- Days header -->
    <div class="grid grid-cols-7 gap-1 mb-1">
      <div v-for="day in days" :key="day" class="text-center text-xs font-medium text-gray-500 py-1">
        {{ day }}
      </div>
    </div>

    <!-- Calendar grid -->
    <div class="grid grid-cols-7 gap-1">
      <div
        v-for="(cell, index) in calendarDays"
        :key="index"
        :class="[
          'aspect-square flex items-center justify-center text-sm rounded-lg transition-colors',
          cell.day ? getIntensityClass(cell.count) : '',
          cell.date === today ? 'ring-2 ring-primary-500' : ''
        ]"
      >
        <span v-if="cell.day" :class="cell.count > 5 ? 'text-white' : 'text-gray-700'">
          {{ cell.day }}
        </span>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex items-center justify-center gap-2 mt-4 text-xs text-gray-500">
      <span>Moins</span>
      <div class="w-4 h-4 rounded bg-gray-50 border border-gray-200"></div>
      <div class="w-4 h-4 rounded bg-gray-200"></div>
      <div class="w-4 h-4 rounded bg-primary-200"></div>
      <div class="w-4 h-4 rounded bg-primary-400"></div>
      <div class="w-4 h-4 rounded bg-primary-600"></div>
      <span>Plus</span>
    </div>
  </div>
</template>
