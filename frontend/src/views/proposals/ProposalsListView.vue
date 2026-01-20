<script setup>
import { ref, onMounted, computed } from 'vue'
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

const form = ref({
  title: '',
  type: 'tactile',
  description: '',
  is_global: false
})

const selectedType = ref('')

const filteredProposals = computed(() => {
  if (!selectedType.value) {
    return proposalsStore.proposals
  }
  return proposalsStore.proposals.filter(p => p.type === selectedType.value)
})

onMounted(async () => {
  try {
    await proposalsStore.fetchProposals({ limit: 100 })
    await proposalsStore.fetchTypes()
  } finally {
    loading.value = false
  }
})

function openModal(proposal = null) {
  editingProposal.value = proposal
  if (proposal) {
    form.value = {
      title: proposal.title,
      type: proposal.type,
      description: proposal.description || '',
      is_global: proposal.is_global
    }
  } else {
    form.value = {
      title: '',
      type: 'tactile',
      description: '',
      is_global: false
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
    await proposalsStore.fetchProposals({ limit: 100 })
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
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Propositions sensorielles</h1>
        <p class="text-gray-600 mt-1">Activités utilisées pendant les séances Snoezelen</p>
      </div>
      <button @click="openModal()" class="btn-primary">
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
          !selectedType ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
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
          selectedType === type ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
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
          class="card p-4 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="font-medium text-gray-900">{{ proposal.title }}</h3>
              <p v-if="proposal.description" class="text-sm text-gray-500 mt-1 line-clamp-2">
                {{ proposal.description }}
              </p>
            </div>
            <div v-if="canEdit(proposal)" class="flex space-x-1 ml-2">
              <button @click="openModal(proposal)" class="p-1 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </button>
              <button @click="confirmDelete(proposal)" class="p-1 text-gray-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <div class="mt-3 flex items-center justify-between">
            <span :class="proposalsStore.getTypeBadgeClass(proposal.type)">
              {{ proposalsStore.getTypeLabel(proposal.type) }}
            </span>
            <span v-if="proposal.is_global" class="text-xs text-gray-400">Globale</span>
          </div>
        </div>
      </div>

      <div v-if="filteredProposals.length === 0" class="text-center py-12 text-gray-500">
        Aucune proposition trouvée
      </div>
    </template>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/50" @click="closeModal" />
        <div class="flex min-h-full items-center justify-center p-4">
          <form @submit.prevent="handleSubmit" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              {{ editingProposal ? 'Modifier la proposition' : 'Nouvelle proposition' }}
            </h3>

            <AlertMessage v-if="error" type="error" class="mb-4">{{ error }}</AlertMessage>

            <div class="space-y-4">
              <div>
                <label class="label">Titre *</label>
                <input v-model="form.title" type="text" class="input" required />
              </div>

              <div>
                <label class="label">Type *</label>
                <select v-model="form.type" class="input">
                  <option v-for="(label, type) in proposalsStore.typeLabels" :key="type" :value="type">
                    {{ label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="label">Description</label>
                <textarea v-model="form.description" rows="3" class="input"></textarea>
              </div>

              <div v-if="authStore.isAdmin" class="flex items-center">
                <input
                  id="is_global"
                  v-model="form.is_global"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                />
                <label for="is_global" class="ml-2 text-sm text-gray-700">
                  Proposition globale (visible par tous)
                </label>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="btn-secondary">Annuler</button>
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
