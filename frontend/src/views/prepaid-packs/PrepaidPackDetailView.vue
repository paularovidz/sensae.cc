<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { prepaidPacksApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const pack = ref(null)
const usages = ref([])
const labels = ref({})
const error = ref('')
const confirmDialog = ref(null)
const editMode = ref(false)

const editForm = ref({
  sessions_total: 0,
  admin_notes: '',
  expires_at: '',
  is_active: true
})

const packStatus = computed(() => {
  if (!pack.value) return null
  if (!pack.value.is_active) {
    return { label: 'Inactif', class: 'bg-gray-700 text-gray-400' }
  }
  if (pack.value.is_expired) {
    return { label: 'Expiré', class: 'bg-red-900/50 text-red-400' }
  }
  if (pack.value.is_exhausted) {
    return { label: 'Épuisé', class: 'bg-yellow-900/50 text-yellow-400' }
  }
  return { label: 'Actif', class: 'bg-green-900/50 text-green-400' }
})

onMounted(async () => {
  await loadPack()
})

async function loadPack() {
  loading.value = true
  try {
    const response = await prepaidPacksApi.getById(route.params.id)
    pack.value = response.data.data.pack
    usages.value = response.data.data.usages || []
    labels.value = response.data.data.labels || {}

    // Init edit form
    editForm.value = {
      sessions_total: pack.value.sessions_total,
      admin_notes: pack.value.admin_notes || '',
      expires_at: pack.value.expires_at ? pack.value.expires_at.split(' ')[0] : '',
      is_active: pack.value.is_active
    }
  } catch (e) {
    console.error('Error loading pack:', e)
    error.value = 'Erreur lors du chargement du pack'
  } finally {
    loading.value = false
  }
}

function startEdit() {
  editMode.value = true
}

function cancelEdit() {
  editMode.value = false
  // Reset form
  editForm.value = {
    sessions_total: pack.value.sessions_total,
    admin_notes: pack.value.admin_notes || '',
    expires_at: pack.value.expires_at ? pack.value.expires_at.split(' ')[0] : '',
    is_active: pack.value.is_active
  }
}

async function saveEdit() {
  saving.value = true
  error.value = ''

  try {
    const updateData = {}

    if (editForm.value.sessions_total !== pack.value.sessions_total) {
      updateData.sessions_total = editForm.value.sessions_total
    }
    if (editForm.value.admin_notes !== (pack.value.admin_notes || '')) {
      updateData.admin_notes = editForm.value.admin_notes
    }
    if (editForm.value.expires_at !== (pack.value.expires_at?.split(' ')[0] || '')) {
      updateData.expires_at = editForm.value.expires_at || null
    }
    if (editForm.value.is_active !== pack.value.is_active) {
      updateData.is_active = editForm.value.is_active
    }

    if (Object.keys(updateData).length === 0) {
      editMode.value = false
      return
    }

    await prepaidPacksApi.update(pack.value.id, updateData)
    await loadPack()
    editMode.value = false
  } catch (e) {
    console.error('Error updating pack:', e)
    error.value = e.response?.data?.message || 'Erreur lors de la mise à jour'
  } finally {
    saving.value = false
  }
}

function confirmDelete() {
  confirmDialog.value?.open()
}

async function handleDelete() {
  try {
    await prepaidPacksApi.delete(pack.value.id)
    router.push('/app/prepaid-packs')
  } catch (e) {
    console.error('Error deleting pack:', e)
    error.value = e.response?.data?.message || 'Erreur lors de la suppression'
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatPrice(price) {
  return Number(price).toFixed(2).replace('.', ',') + ' €'
}
</script>

<template>
  <div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
      <button
        @click="router.push('/app/prepaid-packs')"
        class="flex items-center text-gray-400 hover:text-gray-200 mb-4 transition-colors"
      >
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour à la liste
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <LoadingSpinner />
    </div>

    <!-- Error -->
    <div v-else-if="error && !pack" class="bg-red-900/50 border border-red-700 text-red-400 p-4 rounded-lg">
      {{ error }}
    </div>

    <!-- Content -->
    <div v-else-if="pack" class="space-y-6">
      <!-- Pack Info Card -->
      <div class="bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-700">
        <div class="p-6 border-b border-gray-700">
          <div class="flex items-start justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-100">
                {{ labels.pack_type?.[pack.pack_type] || pack.pack_type }}
              </h1>
              <p class="text-gray-400 mt-1">
                Pack pour {{ pack.user_first_name }} {{ pack.user_last_name }}
              </p>
            </div>
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
              :class="packStatus?.class"
            >
              {{ packStatus?.label }}
            </span>
          </div>

          <!-- Error in edit mode -->
          <div v-if="error && editMode" class="mt-4 p-3 bg-red-900/50 border border-red-700 text-red-400 rounded-lg text-sm">
            {{ error }}
          </div>
        </div>

        <div class="p-6">
          <!-- View Mode -->
          <div v-if="!editMode" class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
              <div class="text-sm text-gray-500 mb-1">Client</div>
              <RouterLink
                :to="`/app/users/${pack.user_id}`"
                class="text-teal-400 hover:underline font-medium"
              >
                {{ pack.user_first_name }} {{ pack.user_last_name }}
              </RouterLink>
              <div class="text-sm text-gray-500">{{ pack.user_email }}</div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Crédits restants</div>
              <div class="text-2xl font-bold" :class="pack.sessions_remaining > 0 ? 'text-teal-400' : 'text-gray-500'">
                {{ pack.sessions_remaining }} / {{ pack.sessions_total }}
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Prix payé</div>
              <div class="text-xl font-bold text-gray-100">{{ formatPrice(pack.price_paid) }}</div>
              <div class="text-sm text-gray-500">
                {{ (pack.price_paid / pack.sessions_total).toFixed(2).replace('.', ',') }} € / séance
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Expiration</div>
              <div class="font-medium" :class="pack.is_expired ? 'text-red-400' : 'text-gray-100'">
                {{ pack.expires_at ? formatDate(pack.expires_at) : 'Sans limite' }}
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Type applicable</div>
              <div class="font-medium text-gray-100">
                {{ labels.duration_type?.[pack.duration_type] || pack.duration_type }}
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Date d'achat</div>
              <div class="font-medium text-gray-100">{{ formatDateTime(pack.purchased_at) }}</div>
            </div>

            <div v-if="pack.creator_first_name" class="col-span-2">
              <div class="text-sm text-gray-500 mb-1">Créé par</div>
              <div class="font-medium text-gray-100">
                {{ pack.creator_first_name }} {{ pack.creator_last_name }}
              </div>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-else class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">
                  Nombre total de séances
                </label>
                <input
                  v-model.number="editForm.sessions_total"
                  type="number"
                  :min="pack.sessions_used"
                  class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                />
                <div class="text-xs text-gray-500 mt-1">
                  Minimum: {{ pack.sessions_used }} (séances déjà utilisées)
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">
                  Date d'expiration
                </label>
                <input
                  v-model="editForm.expires_at"
                  type="date"
                  class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Notes admin</label>
              <textarea
                v-model="editForm.admin_notes"
                rows="2"
                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
              ></textarea>
            </div>

            <div>
              <label class="inline-flex items-center gap-2">
                <input
                  v-model="editForm.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-teal-600 bg-gray-700 border-gray-600 rounded focus:ring-teal-500"
                />
                <span class="text-sm text-gray-300">Pack actif</span>
              </label>
            </div>
          </div>

          <!-- Admin Notes (view mode) -->
          <div v-if="!editMode && pack.admin_notes" class="mt-6 pt-6 border-t border-gray-700">
            <div class="text-sm text-gray-500 mb-1">Notes admin</div>
            <div class="text-gray-300 whitespace-pre-wrap">{{ pack.admin_notes }}</div>
          </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-800/50 border-t border-gray-700 flex justify-between">
          <div v-if="!editMode">
            <button
              @click="confirmDelete"
              class="text-red-400 hover:text-red-300 text-sm transition-colors"
            >
              {{ pack.sessions_used > 0 ? 'Désactiver le pack' : 'Supprimer le pack' }}
            </button>
          </div>
          <div v-else></div>

          <div class="flex gap-3">
            <template v-if="!editMode">
              <button
                @click="startEdit"
                class="px-4 py-2 bg-gray-700 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-600 transition"
              >
                Modifier
              </button>
            </template>
            <template v-else>
              <button
                @click="cancelEdit"
                class="px-4 py-2 bg-gray-700 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-600 transition"
              >
                Annuler
              </button>
              <button
                @click="saveEdit"
                :disabled="saving"
                class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition disabled:opacity-50 flex items-center gap-2"
              >
                <LoadingSpinner v-if="saving" size="sm" />
                Enregistrer
              </button>
            </template>
          </div>
        </div>
      </div>

      <!-- Usage History -->
      <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
        <div class="p-6 border-b border-gray-700">
          <h2 class="text-lg font-semibold text-gray-100">Historique d'utilisation</h2>
        </div>

        <div v-if="usages.length === 0" class="p-6 text-center text-gray-500">
          Aucune séance utilisée pour le moment
        </div>

        <div v-else class="divide-y divide-gray-700">
          <div
            v-for="usage in usages"
            :key="usage.id"
            class="p-4 hover:bg-gray-700/50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium text-gray-100">
                  {{ usage.person_first_name }} {{ usage.person_last_name }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ formatDateTime(usage.session_date) }}
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-500">
                  Utilisé le {{ formatDateTime(usage.used_at) }}
                </div>
                <RouterLink
                  :to="`/app/sessions/${usage.session_id}`"
                  class="text-sm text-teal-400 hover:underline"
                >
                  Voir la séance
                </RouterLink>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Dialog -->
    <ConfirmDialog
      ref="confirmDialog"
      :title="pack?.sessions_used > 0 ? 'Désactiver le pack' : 'Supprimer le pack'"
      :message="pack?.sessions_used > 0
        ? 'Ce pack a des séances utilisées. Il sera désactivé mais conservé pour l\'historique.'
        : 'Voulez-vous vraiment supprimer ce pack ? Cette action est irréversible.'"
      confirmLabel="Confirmer"
      cancelLabel="Annuler"
      @confirm="handleDelete"
    />
  </div>
</template>
