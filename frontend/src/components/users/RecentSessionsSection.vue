<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { usersApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const props = defineProps({
  userId: {
    type: String,
    required: true
  }
})

const loading = ref(true)
const sessions = ref([])
const error = ref(null)

const statusLabels = {
  pending: 'En attente',
  confirmed: 'Confirmé',
  completed: 'Effectué',
  cancelled: 'Annulé',
  no_show: 'Absent'
}

const statusClasses = {
  pending: 'bg-yellow-900/50 text-yellow-400 border-yellow-700/50',
  confirmed: 'bg-blue-900/50 text-blue-400 border-blue-700/50',
  completed: 'bg-green-900/50 text-green-400 border-green-700/50',
  cancelled: 'bg-gray-700/50 text-gray-400 border-gray-600/50',
  no_show: 'bg-red-900/50 text-red-400 border-red-700/50'
}

onMounted(async () => {
  try {
    const response = await usersApi.getRecentSessions(props.userId, 10)
    sessions.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching recent sessions:', e)
    error.value = 'Impossible de charger les sessions récentes'
  } finally {
    loading.value = false
  }
})

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatCreatedAt(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}
</script>

<template>
  <div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
      <h2 class="font-semibold text-white">Dernières réservations</h2>
      <span class="text-xs text-gray-500">Triées par date de réservation</span>
    </div>

    <div v-if="loading" class="p-6">
      <LoadingSpinner size="sm" />
    </div>

    <div v-else-if="error" class="p-6">
      <p class="text-red-400 text-sm">{{ error }}</p>
    </div>

    <div v-else-if="sessions.length === 0" class="p-6">
      <p class="text-gray-500 text-sm">Aucune réservation trouvée</p>
    </div>

    <div v-else class="divide-y divide-gray-700">
      <RouterLink
        v-for="session in sessions"
        :key="session.id"
        :to="`/app/sessions/${session.id}`"
        class="flex items-center px-6 py-4 hover:bg-gray-700/50 transition-colors"
      >
        <!-- Avatar / Initials -->
        <div class="w-10 h-10 rounded-full bg-primary-900/50 flex items-center justify-center text-primary-400 font-semibold flex-shrink-0">
          {{ session.person_first_name?.charAt(0) }}{{ session.person_last_name?.charAt(0) }}
        </div>

        <!-- Session info -->
        <div class="ml-4 flex-1 min-w-0">
          <div class="font-medium text-white truncate">
            {{ session.person_first_name }} {{ session.person_last_name }}
          </div>
          <div class="text-sm text-gray-400 flex items-center gap-2">
            <span>{{ formatDate(session.session_date) }}</span>
            <span class="text-gray-600">-</span>
            <span>{{ session.duration_minutes }} min</span>
          </div>
          <div class="text-xs text-gray-500 mt-0.5">
            Réservé le {{ formatCreatedAt(session.created_at) }}
          </div>
        </div>

        <!-- Status badge -->
        <div class="ml-4 flex-shrink-0">
          <span
            :class="statusClasses[session.status] || statusClasses.pending"
            class="px-2 py-1 text-xs rounded-full border"
          >
            {{ statusLabels[session.status] || session.status }}
          </span>
        </div>

        <!-- Arrow -->
        <svg class="w-5 h-5 text-gray-500 ml-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </RouterLink>
    </div>
  </div>
</template>
