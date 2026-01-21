<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useProposalsStore } from '@/stores/proposals'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import AlertMessage from '@/components/ui/AlertMessage.vue'

const proposalsStore = useProposalsStore()
const authStore = useAuthStore()

const loading = ref(true)
const showModal = ref(false)
const editingProposal = ref(null)
const confirmDialog = ref(null)
const proposalToDelete = ref(null)
const error = ref('')
const searchQuery = ref('')
let searchTimeout = null

const form = ref({
  title: '',
  type: 'tactile',
  description: ''
})

const selectedType = ref('')

const filteredProposals = computed(() => {
  return proposalsStore.proposals
})

onMounted(async () => {
  try {
    await proposalsStore.fetchProposals({ limit: 100 })
    await proposalsStore.fetchTypes()
  } finally {
    loading.value = false
  }
})

// Debounced search
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadProposals()
  }, 300)
})

// Reload when type changes
watch(selectedType, () => {
  loadProposals()
})

async function loadProposals() {
  loading.value = true
  try {
    const params = { limit: 100 }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    if (selectedType.value) {
      params.type = selectedType.value
    }
    await proposalsStore.fetchProposals(params)
  } finally {
    loading.value = false
  }
}

function openModal(proposal = null) {
  editingProposal.value = proposal
  if (proposal) {
    form.value = {
      title: proposal.title,
      type: proposal.type,
      description: proposal.description || ''
    }
  } else {
    form.value = {
      title: '',
      type: 'tactile',
      description: ''
    }
  }
  showModal.value = true
  error.value = ''
}

function closeModal() {
  showModal.value = false
  editingProposal.value = null
  error.value = ''
}

async function handleSubmit() {
  try {
    if (editingProposal.value) {
      await proposalsStore.updateProposal(editingProposal.value.id, form.value)
    } else {
      await proposalsStore.createProposal(form.value)
    }
    closeModal()
    await loadProposals()
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue'
  }
}

function confirmDelete(proposal) {
  proposalToDelete.value = proposal
  confirmDialog.value?.open()
}

async function handleDelete() {
  if (!proposalToDelete.value) return
  try {
    await proposalsStore.deleteProposal(proposalToDelete.value.id)
  } catch (e) {
    console.error('Error deleting proposal:', e)
  }
  proposalToDelete.value = null
}

function canEdit(proposal) {
  return authStore.isAdmin || proposal.created_by === authStore.user?.id
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="relative flex-1 max-w-md">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher par titre ou description..."
          class="w-full pl-10 pr-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <button @click="openModal()" class="btn-primary whitespace-nowrap">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nouvelle proposition
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-2">
      <button
        @click="selectedType = ''"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          !selectedType ? 'bg-primary-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        ]"
      >
        Toutes
      </button>
      <button
        v-for="(label, type) in proposalsStore.typeLabels"
        :key="type"
        @click="selectedType = type"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          selectedType === type ? 'bg-primary-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        ]"
      >
        {{ label }}
      </button>
    </div>

    <LoadingSpinner v-if="loading" size="lg" class="py-12" />

    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="proposal in filteredProposals"
          :key="proposal.id"
          class="bg-gray-800 rounded-xl border border-gray-700 p-4 hover:border-gray-600 transition-colors"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="font-medium text-white">{{ proposal.title }}</h3>
              <p v-if="proposal.description" class="text-sm text-gray-400 mt-1 line-clamp-2">
                {{ proposal.description }}
              </p>
            </div>
            <div v-if="canEdit(proposal)" class="flex space-x-1 ml-2">
              <button @click="openModal(proposal)" class="p-1 text-gray-400 hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </button>
              <button @click="confirmDelete(proposal)" class="p-1 text-gray-400 hover:text-red-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <div class="mt-3">
            <span :class="proposalsStore.getTypeBadgeClass(proposal.type)">
              {{ proposalsStore.getTypeLabel(proposal.type) }}
            </span>
          </div>
        </div>
      </div>

      <div v-if="filteredProposals.length === 0" class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="mt-4 text-gray-400">
          {{ searchQuery || selectedType ? 'Aucune proposition ne correspond à vos critères' : 'Aucune proposition trouvée' }}
        </p>
      </div>
    </template>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/50" @click="closeModal" />
        <div class="flex min-h-full items-center justify-center p-4">
          <form @submit.prevent="handleSubmit" class="relative bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-700">
            <h3 class="text-lg font-semibold text-white mb-4">
              {{ editingProposal ? 'Modifier la proposition' : 'Nouvelle proposition' }}
            </h3>

            <AlertMessage v-if="error" type="error" class="mb-4">{{ error }}</AlertMessage>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Titre *</label>
                <input v-model="form.title" type="text" class="w-full px-4 py-2 text-sm bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Type *</label>
                <select v-model="form.type" class="w-full px-4 py-2 text-sm bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                  <option v-for="(label, type) in proposalsStore.typeLabels" :key="type" :value="type">
                    {{ label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 text-sm bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="bg-gray-700 text-gray-200 border border-gray-600 hover:bg-gray-600 px-4 py-2 text-sm font-medium rounded-lg transition-colors">Annuler</button>
              <button type="submit" class="btn-primary">
                {{ editingProposal ? 'Enregistrer' : 'Créer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog
      ref="confirmDialog"
      title="Supprimer cette proposition ?"
      :message="`Êtes-vous sûr de vouloir supprimer '${proposalToDelete?.title}' ?`"
      confirm-text="Supprimer"
      danger
      @confirm="handleDelete"
    />
  </div>
</template>
