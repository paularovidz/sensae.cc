<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePersonsStore } from '@/stores/persons'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { personsApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import DocumentsSection from '@/components/documents/DocumentsSection.vue'
import SessionDocumentsList from '@/components/documents/SessionDocumentsList.vue'
import ImpersonationBanner from '@/components/ui/ImpersonationBanner.vue'

const route = useRoute()
const router = useRouter()
const personsStore = usePersonsStore()
const authStore = useAuthStore()
const toastStore = useToastStore()

const loading = ref(true)
const sessions = ref([])
const sessionsLoading = ref(false)
const sessionsPagination = ref({ page: 1, total: 0, pages: 0 })
const confirmDialog = ref(null)

const person = computed(() => personsStore.currentPerson)

// Can delete only if no sessions
const canDelete = computed(() => sessionsPagination.value.total === 0)

onMounted(async () => {
  try {
    await personsStore.fetchPerson(route.params.id)
    await loadSessions()
  } catch (e) {
    console.error('Error loading person:', e)
    router.push('/app/member')
  } finally {
    loading.value = false
  }
})

async function loadSessions(page = 1) {
  if (!route.params.id) return

  sessionsLoading.value = true
  try {
    const response = await personsApi.getSessions(route.params.id, { page, limit: 20 })
    sessions.value = response.data.data.sessions
    sessionsPagination.value = response.data.data.pagination
  } catch (e) {
    console.error('Error loading sessions:', e)
  } finally {
    sessionsLoading.value = false
  }
}

function viewSession(sessionId) {
  router.push(`/app/member/sessions/${sessionId}`)
}

function goBack() {
  router.push('/app/member')
}

function confirmDelete() {
  confirmDialog.value?.open()
}

async function handleDelete() {
  try {
    await personsApi.delete(route.params.id)
    toastStore.success('Personne supprimee avec succes')
    router.push('/app/member')
  } catch (e) {
    console.error('Error deleting person:', e)
    toastStore.error(e.response?.data?.message || 'Erreur lors de la suppression')
  }
}

function formatDateTime(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const behaviorLabels = {
  calm: 'Calme',
  agitated: 'Agite',
  tired: 'Fatigue',
  defensive: 'Defensif',
  anxious: 'Inquiet',
  passive: 'Passif'
}

function getBehaviorBadgeClass(behavior) {
  const classes = {
    calm: 'badge-success',
    agitated: 'badge-warning',
    tired: 'badge-gray',
    defensive: 'badge-danger',
    anxious: 'badge-warning',
    passive: 'badge-gray'
  }
  return classes[behavior] || 'badge-gray'
}

async function handleLogout() {
  await authStore.logout()
}
</script>

<template>
  <div class="min-h-screen bg-dark">
    <!-- Impersonation Banner -->
    <ImpersonationBanner />

    <!-- Header -->
    <header class="header-dark px-4 py-4">
      <div class="max-w-4xl mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
            <span class="text-white text-lg font-bold">S</span>
          </div>
          <h1 class="text-xl font-semibold text-white">sensea</h1>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-400">{{ authStore.fullName }}</span>
          <button @click="handleLogout" class="text-sm text-red-400 hover:text-red-300 transition-colors">
            Deconnexion
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto p-4">
      <LoadingSpinner v-if="loading" size="lg" class="py-12" />

      <template v-else-if="person">
        <!-- Bouton retour -->
        <button
          @click="goBack"
          class="flex items-center text-gray-400 hover:text-white mb-4 transition-colors"
        >
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Retour a l'accueil
        </button>

        <!-- Infos personne -->
        <div class="card-dark p-6 mb-6">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-xl font-bold text-white">
                {{ person.first_name }} {{ person.last_name }}
              </h2>
              <div class="mt-2 text-gray-300">
                <span v-if="person.birth_date">
                  Ne(e) le {{ formatDate(person.birth_date) }}
                  <span v-if="person.age"> ({{ person.age }} ans)</span>
                </span>
              </div>
              <div class="mt-2 flex items-center space-x-4 text-sm text-gray-400">
                <span>{{ sessionsPagination.total }} seance(s)</span>
                <span v-if="person.stats?.average_duration">
                  Duree moyenne : {{ person.stats.average_duration }} min
                </span>
              </div>
            </div>
            <button
              v-if="canDelete"
              @click="confirmDelete"
              class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-900/30 rounded-lg transition-colors"
              title="Supprimer cette personne"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Documents de la personne -->
        <div class="mb-6">
          <DocumentsSection
            type="person"
            :entity-id="person.id"
            :title="`Documents de ${person.first_name}`"
            :can-upload="false"
            :current-user-id="authStore.user?.id"
            dark
          />
        </div>

        <!-- Documents des seances de la personne -->
        <div class="mb-6">
          <SessionDocumentsList
            :person-id="person.id"
            :title="`Factures des seances de ${person.first_name}`"
          />
        </div>

        <!-- Liste des seances -->
        <h3 class="font-semibold text-white mb-4">Historique des seances</h3>

        <LoadingSpinner v-if="sessionsLoading" size="md" class="py-8" />

        <EmptyState
          v-else-if="sessions.length === 0"
          title="Aucune seance"
          description="Aucune seance n'a encore ete enregistree pour cette personne."
          icon="calendar"
          class="py-8"
          dark
        />

        <div v-else class="space-y-3">
          <button
            v-for="session in sessions"
            :key="session.id"
            @click="viewSession(session.id)"
            class="w-full card-dark-interactive p-4 text-left"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium text-white">
                  {{ formatDateTime(session.session_date) }}
                </div>
                <div class="text-sm text-gray-400 mt-1">
                  {{ session.duration_minutes }} minutes
                  <span v-if="session.creator_first_name">
                    - par {{ session.creator_first_name }} {{ session.creator_last_name }}
                  </span>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span v-if="session.behavior_end" :class="getBehaviorBadgeClass(session.behavior_end)">
                  {{ behaviorLabels[session.behavior_end] }}
                </span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="sessionsPagination.pages > 1" class="mt-6 flex justify-center">
          <div class="flex space-x-2">
            <button
              v-for="page in sessionsPagination.pages"
              :key="page"
              @click="loadSessions(page)"
              :class="[
                'px-3 py-1 text-sm rounded-lg transition-colors',
                page === sessionsPagination.page
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </template>
    </main>

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      ref="confirmDialog"
      title="Supprimer cette personne ?"
      :message="`Etes-vous sur de vouloir supprimer ${person?.first_name} ${person?.last_name} ? Cette action est irreversible.`"
      confirm-text="Supprimer"
      danger
      @confirm="handleDelete"
    />
  </div>
</template>
