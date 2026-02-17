<template>
  <div class="bg-gray-700/30 rounded-xl border border-gray-600/50 overflow-hidden">
    <div class="px-4 py-3 bg-gray-700/50 border-b border-gray-600/50">
      <h3 class="text-sm font-medium text-gray-200">
        Créneaux disponibles pour le {{ formattedDate }}
      </h3>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="p-4">
      <!-- Morning skeleton -->
      <div class="mb-4">
        <div class="h-3 w-12 bg-gray-600/50 rounded animate-pulse mb-2" />
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
          <div
            v-for="n in 4"
            :key="'skeleton-morning-' + n"
            class="h-10 rounded-lg bg-gray-700/50 animate-pulse"
          />
        </div>
      </div>

      <!-- Afternoon skeleton -->
      <div>
        <div class="h-3 w-16 bg-gray-600/50 rounded animate-pulse mb-2" />
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
          <div
            v-for="n in 4"
            :key="'skeleton-afternoon-' + n"
            class="h-10 rounded-lg bg-gray-700/50 animate-pulse"
          />
        </div>
      </div>
    </div>

    <!-- No slots -->
    <div v-else-if="availableSlots.length === 0" class="p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="mt-2 text-sm text-gray-400">Aucun créneau disponible pour cette date</p>
    </div>

    <!-- Slots grid -->
    <div v-else class="p-4">
      <!-- Group session slots (larger buttons with labels) -->
      <template v-if="isGroupSession">
        <div class="space-y-3">
          <button
            v-for="slot in availableSlots"
            :key="slot.time"
            @click="selectSlot(slot)"
            :disabled="!slot.available"
            :class="[
              'w-full p-4 rounded-lg text-left transition-all duration-200',
              getSlotClasses(slot)
            ]"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">{{ getGroupSlotLabel(slot) }}</p>
                <p class="text-sm opacity-75">Début à {{ slot.time }}</p>
              </div>
              <div v-if="selectedTime === slot.time" class="text-indigo-400">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </button>
        </div>
      </template>

      <!-- Individual session slots (time grid) -->
      <template v-else>
        <!-- Morning -->
        <div v-if="morningSlots.length > 0" class="mb-4">
          <h4 class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Matin</h4>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <button
              v-for="slot in morningSlots"
              :key="slot.time"
              @click="selectSlot(slot)"
              :disabled="!slot.available"
              :class="[
                'py-2.5 px-4 rounded-lg text-sm font-medium transition-all duration-200',
                getSlotClasses(slot)
              ]"
            >
              {{ slot.time }}
            </button>
          </div>
        </div>

        <!-- Afternoon -->
        <div v-if="afternoonSlots.length > 0">
          <h4 class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Après-midi</h4>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <button
              v-for="slot in afternoonSlots"
              :key="slot.time"
              @click="selectSlot(slot)"
              :disabled="!slot.available"
              :class="[
                'py-2.5 px-4 rounded-lg text-sm font-medium transition-all duration-200',
                getSlotClasses(slot)
              ]"
            >
              {{ slot.time }}
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Duration info -->
    <div class="px-4 py-3 bg-gray-700/50 border-t border-gray-600/50">
      <p class="text-xs text-gray-400 text-center">
        Durée de la séance : <strong class="text-gray-200">{{ formattedDuration }}</strong>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  date: {
    type: String,
    required: true
  },
  selectedTime: {
    type: String,
    default: null
  },
  slots: {
    type: Array,
    default: () => []
  },
  durationMinutes: {
    type: Number,
    default: 45
  },
  loading: {
    type: Boolean,
    default: false
  },
  isGroupSession: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:selectedTime'])

// Only show available slots (backend already filters them)
const availableSlots = computed(() => props.slots.filter(slot => slot.available))

const morningSlots = computed(() => {
  return availableSlots.value.filter(slot => {
    const hour = parseInt(slot.time.split(':')[0])
    return hour < 13
  })
})

const afternoonSlots = computed(() => {
  return availableSlots.value.filter(slot => {
    const hour = parseInt(slot.time.split(':')[0])
    return hour >= 13
  })
})

const formattedDate = computed(() => {
  if (!props.date) return ''
  const [year, month, day] = props.date.split('-')
  const date = new Date(year, month - 1, day)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
  })
})

const formattedDuration = computed(() => {
  const mins = props.durationMinutes
  if (mins === 75) return '1h15'
  if (mins === 45) return '45 min'
  if (mins >= 60) {
    const h = Math.floor(mins / 60)
    const m = mins % 60
    return m > 0 ? `${h}h${m.toString().padStart(2, '0')}` : `${h}h`
  }
  return `${mins} min`
})

function getSlotClasses(slot) {
  const isSelected = props.selectedTime === slot.time

  if (isSelected) {
    return 'bg-indigo-600 text-white ring-2 ring-indigo-500 ring-offset-2 ring-offset-gray-800'
  }

  if (slot.available) {
    return 'bg-gray-600/50 border border-gray-500/50 text-gray-200 hover:border-indigo-400 hover:bg-indigo-500/20'
  }

  return 'bg-gray-700/30 text-gray-500 cursor-not-allowed line-through'
}

function selectSlot(slot) {
  if (slot.available) {
    emit('update:selectedTime', slot.time)
  }
}

function getGroupSlotLabel(slot) {
  const hour = parseInt(slot.time.split(':')[0])
  // For full day sessions, there's only one slot at opening
  if (props.durationMinutes >= 480) {
    return 'Journée complète'
  }
  // For half day sessions, morning or afternoon
  if (hour < 13) {
    return 'Matin'
  }
  return 'Après-midi'
}
</script>
