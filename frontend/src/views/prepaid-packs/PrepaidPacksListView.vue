<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { prepaidPacksApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'

const router = useRouter()

const loading = ref(true)
const packs = ref([])
const pagination = ref({ page: 1, limit: 20, total: 0, pages: 0 })
const labels = ref({ pack_type: {}, duration_type: {} })
const confirmDialog = ref(null)
const packToDelete = ref(null)
const searchQuery = ref('')
const filterActive = ref(null)
const filterHasCredits = ref(false)
let searchTimeout = null

onMounted(async () => {
  await loadPacks()
})

// Debounced search
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadPacks(1)
  }, 300)
})

watch([filterActive, filterHasCredits], () => {
  loadPacks(1)
})

async function loadPacks(page = 1) {
  loading.value = true
  try {
    const params = { page, limit: 20 }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    if (filterActive.value !== null) {
      params.is_active = filterActive.value
    }
    if (filterHasCredits.value) {
      params.has_credits = true
      params.not_expired = true
    }
    const response = await prepaidPacksApi.getAll(params)
    packs.value = response.data.data.packs
    pagination.value = response.data.data.pagination
    labels.value = response.data.data.labels
  } catch (e) {
    console.error('Error loading prepaid packs:', e)
  } finally {
    loading.value = false
  }
}

function confirmDelete(pack) {
  packToDelete.value = pack
  confirmDialog.value?.open()
}

async function handleDelete() {
  if (!packToDelete.value) return
  try {
    await prepaidPacksApi.delete(packToDelete.value.id)
    await loadPacks(pagination.value.page)
  } catch (e) {
    console.error('Error deleting pack:', e)
    alert(e.response?.data?.message || 'Erreur lors de la suppression')
  } finally {
    packToDelete.value = null
  }
}

function goToPack(packId) {
  router.push(`/app/prepaid-packs/${packId}`)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

function formatPrice(price) {
  return Number(price).toFixed(2).replace('.', ',') + ' €'
}

function getPackStatus(pack) {
  if (!pack.is_active) {
    return { label: 'Inactif', class: 'bg-gray-700 text-gray-400' }
  }
  if (pack.is_expired) {
    return { label: 'Expiré', class: 'bg-red-900/50 text-red-400' }
  }
  if (pack.is_exhausted) {
    return { label: 'Épuisé', class: 'bg-yellow-900/50 text-yellow-400' }
  }
  return { label: 'Actif', class: 'bg-green-900/50 text-green-400' }
}
</script>

<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-100">Packs prépayés</h1>
        <p class="text-gray-400 text-sm mt-1">Gérez les packs de séances prépayées</p>
      </div>
      <RouterLink
        to="/app/prepaid-packs/new"
        class="inline-flex items-center justify-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau pack
      </RouterLink>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-gray-700">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm text-gray-400 mb-1">Rechercher</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nom, email..."
            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-gray-100 placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          />
        </div>

        <!-- Filter: Active -->
        <div>
          <label class="block text-sm text-gray-400 mb-1">Statut</label>
          <select
            v-model="filterActive"
            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
            <option :value="null">Tous</option>
            <option :value="true">Actifs</option>
            <option :value="false">Inactifs</option>
          </select>
        </div>

        <!-- Filter: Has credits -->
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2 cursor-pointer">
            <input
              v-model="filterHasCredits"
              type="checkbox"
              class="w-4 h-4 text-teal-600 bg-gray-700 border-gray-600 rounded focus:ring-teal-500"
            />
            <span class="text-sm text-gray-300">Crédits disponibles uniquement</span>
          </label>
        </div>

        <!-- Total -->
        <div class="flex items-end justify-end text-sm text-gray-400">
          {{ pagination.total }} pack{{ pagination.total > 1 ? 's' : '' }} trouvé{{ pagination.total > 1 ? 's' : '' }}
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <LoadingSpinner />
    </div>

    <!-- Table -->
    <div v-else-if="packs.length > 0" class="bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-gray-800/50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Client</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Pack</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Crédits</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Prix payé</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Statut</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Expiration</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr
              v-for="pack in packs"
              :key="pack.id"
              class="hover:bg-gray-700/50 cursor-pointer transition-colors"
              @click="goToPack(pack.id)"
            >
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-100">
                  {{ pack.user_first_name }} {{ pack.user_last_name }}
                </div>
                <div class="text-xs text-gray-500">{{ pack.user_email }}</div>
              </td>
              <td class="px-4 py-3">
                <span class="text-sm font-medium text-gray-100">
                  {{ labels.pack_type[pack.pack_type] || pack.pack_type }}
                </span>
                <div class="text-xs text-gray-500">
                  {{ labels.duration_type[pack.duration_type] || pack.duration_type }}
                </div>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium"
                  :class="pack.sessions_remaining > 0 ? 'bg-teal-900/50 text-teal-400' : 'bg-gray-700 text-gray-400'"
                >
                  {{ pack.sessions_remaining }}/{{ pack.sessions_total }}
                </span>
              </td>
              <td class="px-4 py-3 text-right text-sm font-medium text-gray-100">
                {{ formatPrice(pack.price_paid) }}
              </td>
              <td class="px-4 py-3 text-center">
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getPackStatus(pack).class"
                >
                  {{ getPackStatus(pack).label }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-400">
                {{ pack.expires_at ? formatDate(pack.expires_at) : 'Sans limite' }}
              </td>
              <td class="px-4 py-3 text-right" @click.stop>
                <div class="flex items-center justify-end gap-2">
                  <RouterLink
                    :to="`/app/prepaid-packs/${pack.id}`"
                    class="text-gray-500 hover:text-teal-400 transition-colors"
                    title="Voir"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </RouterLink>
                  <button
                    @click="confirmDelete(pack)"
                    class="text-gray-500 hover:text-red-400 transition-colors"
                    title="Supprimer"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.pages > 1" class="px-4 py-3 border-t border-gray-700 flex items-center justify-between">
        <div class="text-sm text-gray-400">
          Page {{ pagination.page }} sur {{ pagination.pages }}
        </div>
        <div class="flex gap-2">
          <button
            @click="loadPacks(pagination.page - 1)"
            :disabled="pagination.page <= 1"
            class="px-3 py-1 bg-gray-700 border border-gray-600 text-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-600 transition-colors"
          >
            Précédent
          </button>
          <button
            @click="loadPacks(pagination.page + 1)"
            :disabled="pagination.page >= pagination.pages"
            class="px-3 py-1 bg-gray-700 border border-gray-600 text-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-600 transition-colors"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else class="bg-gray-800 rounded-lg shadow-sm p-12 text-center border border-gray-700">
      <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
      <h3 class="text-lg font-medium text-gray-100 mb-2">Aucun pack prépayé</h3>
      <p class="text-gray-400 mb-6">Créez votre premier pack de séances prépayées</p>
      <RouterLink
        to="/app/prepaid-packs/new"
        class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition"
      >
        Créer un pack
      </RouterLink>
    </div>

    <!-- Confirm Dialog -->
    <ConfirmDialog
      ref="confirmDialog"
      title="Supprimer le pack"
      :message="`Voulez-vous vraiment ${packToDelete?.sessions_used > 0 ? 'désactiver' : 'supprimer'} ce pack prépayé pour ${packToDelete?.user_first_name} ${packToDelete?.user_last_name} ?`"
      confirmLabel="Confirmer"
      cancelLabel="Annuler"
      @confirm="handleDelete"
    />
  </div>
</template>
