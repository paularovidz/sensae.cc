<script setup>
import { computed } from 'vue'
import { useOpsStore } from '@/stores/ops'

const opsStore = useOpsStore()

const months = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
]

const displayText = computed(() => {
  return `${months[opsStore.currentMonth - 1]} ${opsStore.currentYear}`
})

const emit = defineEmits(['change'])

function handlePrev() {
  opsStore.prevMonth()
  emit('change')
}

function handleNext() {
  opsStore.nextMonth()
  emit('change')
}

function handleToday() {
  opsStore.setMonth(new Date().getFullYear(), new Date().getMonth() + 1)
  emit('change')
}
</script>

<template>
  <div class="flex items-center gap-2">
    <button
      @click="handlePrev"
      class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg"
      title="Mois précédent"
    >
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <button
      @click="handleToday"
      class="px-4 py-2 text-white font-medium bg-gray-700 hover:bg-gray-600 rounded-lg min-w-[160px]"
    >
      {{ displayText }}
    </button>

    <button
      @click="handleNext"
      class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg"
      title="Mois suivant"
    >
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>
  </div>
</template>
