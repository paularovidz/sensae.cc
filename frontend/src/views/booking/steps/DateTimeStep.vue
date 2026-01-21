<template>
  <div class="p-6">
    <h2 class="text-xl font-semibold text-white mb-2">Choisissez votre créneau</h2>
    <p class="text-gray-400 mb-4">
      Sélectionnez une date puis un horaire disponible pour votre {{ durationLabel }}.
    </p>

    <!-- Session type and price info -->
    <div class="mb-6 p-4 bg-gray-700/30 border border-gray-600/50 rounded-lg">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="text-white font-medium">{{ sessionTypeLabel }}</p>
            <p class="text-sm text-gray-400">{{ sessionTypeDescription }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-2xl font-bold text-white">{{ bookingStore.currentPrice }} &euro;</p>
          <p class="text-xs text-gray-400">par séance</p>
        </div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <!-- Calendar -->
      <div>
        <BookingCalendar
          :year="bookingStore.currentYear"
          :month="bookingStore.currentMonth"
          :selected-date="bookingStore.selectedDate"
          :available-dates="bookingStore.availableDates"
          :loading="loadingDates"
          @update:selected-date="selectDate"
          @month-change="handleMonthChange"
        />
      </div>

      <!-- Time slots -->
      <div>
        <template v-if="bookingStore.selectedDate">
          <TimeSlotPicker
            :date="bookingStore.selectedDate"
            :selected-time="bookingStore.selectedTime"
            :slots="bookingStore.availableSlots"
            :duration-minutes="bookingStore.durationInfo.display"
            :loading="loadingSlots"
            @update:selected-time="selectTime"
          />
        </template>
        <template v-else>
          <div class="bg-gray-700/30 rounded-xl border border-gray-600/50 p-8 text-center h-full flex items-center justify-center">
            <div>
              <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="mt-2 text-sm text-gray-400">
                Sélectionnez d'abord une date sur le calendrier
              </p>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Selected slot summary -->
    <div v-if="bookingStore.selectedDate && bookingStore.selectedTime" class="mt-6 p-4 bg-green-900/30 border border-green-500/50 rounded-lg">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span class="text-green-300 font-medium">
          {{ formattedSelection }}
        </span>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useBookingStore } from '@/stores/booking'
import BookingCalendar from '@/components/booking/BookingCalendar.vue'
import TimeSlotPicker from '@/components/booking/TimeSlotPicker.vue'

const bookingStore = useBookingStore()

const loadingDates = ref(false)
const loadingSlots = ref(false)

const durationLabel = computed(() => {
  return bookingStore.durationType === 'discovery'
    ? 'séance découverte (1h15)'
    : 'séance classique (45 min)'
})

const sessionTypeLabel = computed(() => {
  return bookingStore.durationType === 'discovery'
    ? 'Séance découverte'
    : 'Séance classique'
})

const sessionTypeDescription = computed(() => {
  return bookingStore.durationType === 'discovery'
    ? 'Durée : 1h15 - Première séance pour découvrir l\'approche Snoezelen'
    : 'Durée : 45 min - Séance de suivi régulier'
})

const formattedSelection = computed(() => {
  if (!bookingStore.selectedDate || !bookingStore.selectedTime) return ''

  const [year, month, day] = bookingStore.selectedDate.split('-')
  const date = new Date(year, month - 1, day)
  const dateStr = date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })

  return `${dateStr} à ${bookingStore.selectedTime}`
})

onMounted(async () => {
  // Déterminer automatiquement le type de séance :
  // - Nouvelle personne (pas d'ID) = découverte
  // - Personne existante (avec ID) = classique
  const isNewPerson = !bookingStore.selectedPersonId
  const newType = isNewPerson ? 'discovery' : 'regular'

  if (bookingStore.durationType !== newType) {
    // Utiliser setDurationType pour reset correctement toutes les données
    bookingStore.setDurationType(newType)
  }

  await fetchAvailableDates()
})

async function fetchAvailableDates() {
  loadingDates.value = true
  try {
    await bookingStore.fetchAvailableDates()
  } finally {
    loadingDates.value = false
  }
}

async function handleMonthChange({ year, month }) {
  bookingStore.currentYear = year
  bookingStore.currentMonth = month
  await fetchAvailableDates()
}

async function selectDate(date) {
  bookingStore.selectedDate = date
  bookingStore.selectedTime = null

  loadingSlots.value = true
  try {
    await bookingStore.fetchAvailableSlots(date)
  } finally {
    loadingSlots.value = false
  }
}

function selectTime(time) {
  bookingStore.selectedTime = time
  // Passer automatiquement à l'étape suivante après sélection
  nextTick(() => {
    bookingStore.nextStep()
  })
}
</script>
