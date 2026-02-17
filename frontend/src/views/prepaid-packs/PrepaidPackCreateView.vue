<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { prepaidPacksApi, usersApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const error = ref('')
const packTypes = ref({})
const durationTypes = ref([])
const labels = ref({})
const users = ref([])
const searchQuery = ref('')
const loadingUsers = ref(false)
const selectedUser = ref(null)
let searchTimeout = null

const form = ref({
  user_id: '',
  pack_type: 'pack_2',
  duration_type: 'any',
  sessions_total: 2,
  price_paid: 110,
  expires_at: '',
  admin_notes: ''
})

const selectedPackType = computed(() => {
  return packTypes.value[form.value.pack_type] || null
})

const pricePerSession = computed(() => {
  if (!form.value.sessions_total || form.value.sessions_total <= 0) return 0
  return (form.value.price_paid / form.value.sessions_total).toFixed(2)
})

onMounted(async () => {
  try {
    const response = await prepaidPacksApi.getTypes()
    packTypes.value = response.data.data.types
    durationTypes.value = response.data.data.duration_types
    labels.value = response.data.data.labels

    // Set default values from pack type
    if (packTypes.value['pack_2']) {
      form.value.sessions_total = packTypes.value['pack_2'].sessions
      form.value.price_paid = packTypes.value['pack_2'].price
    }

    // Calculate default expiry (12 months from now)
    const expiryDate = new Date()
    expiryDate.setFullYear(expiryDate.getFullYear() + 1)
    form.value.expires_at = expiryDate.toISOString().split('T')[0]
  } catch (e) {
    console.error('Error loading pack types:', e)
    error.value = 'Erreur lors du chargement des types de packs'
  } finally {
    loading.value = false
  }
})

watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  if (!searchQuery.value.trim()) {
    users.value = []
    return
  }
  searchTimeout = setTimeout(searchUsers, 300)
})

watch(() => form.value.pack_type, (newType) => {
  if (packTypes.value[newType]) {
    form.value.sessions_total = packTypes.value[newType].sessions
    form.value.price_paid = packTypes.value[newType].price
  }
})

async function searchUsers() {
  if (!searchQuery.value.trim()) return
  loadingUsers.value = true
  try {
    const response = await usersApi.getAll({
      search: searchQuery.value,
      limit: 10,
      is_active: true
    })
    users.value = response.data.data.users
  } catch (e) {
    console.error('Error searching users:', e)
  } finally {
    loadingUsers.value = false
  }
}

function selectUser(user) {
  form.value.user_id = user.id
  selectedUser.value = user
  searchQuery.value = ''
  users.value = []
}

function clearUser() {
  form.value.user_id = ''
  selectedUser.value = null
}

async function handleSubmit() {
  error.value = ''

  if (!form.value.user_id) {
    error.value = 'Veuillez sélectionner un client'
    return
  }

  if (form.value.sessions_total <= 0) {
    error.value = 'Le nombre de séances doit être supérieur à 0'
    return
  }

  saving.value = true
  try {
    await prepaidPacksApi.create({
      user_id: form.value.user_id,
      pack_type: form.value.pack_type,
      duration_type: form.value.duration_type,
      sessions_total: form.value.sessions_total,
      price_paid: form.value.price_paid,
      expires_at: form.value.expires_at || null,
      admin_notes: form.value.admin_notes || null
    })
    router.push('/app/prepaid-packs')
  } catch (e) {
    console.error('Error creating pack:', e)
    error.value = e.response?.data?.message || 'Erreur lors de la création du pack'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="container mx-auto px-4 py-6 max-w-2xl">
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
      <h1 class="text-2xl font-bold text-gray-100">Nouveau pack prépayé</h1>
      <p class="text-gray-400 text-sm mt-1">Créez un pack de séances prépayées pour un client</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <LoadingSpinner />
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-700">
      <!-- Error -->
      <div v-if="error" class="mb-6 p-4 bg-red-900/50 border border-red-700 text-red-400 rounded-lg">
        {{ error }}
      </div>

      <!-- Client selection -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Client <span class="text-red-400">*</span>
        </label>

        <!-- Selected user -->
        <div v-if="selectedUser" class="flex items-center justify-between p-3 bg-teal-900/30 border border-teal-700 rounded-lg">
          <div>
            <div class="font-medium text-gray-100">
              {{ selectedUser.first_name }} {{ selectedUser.last_name }}
              <span v-if="selectedUser.company_name" class="text-teal-300 ml-1">({{ selectedUser.company_name }})</span>
            </div>
            <div class="text-sm text-gray-400">{{ selectedUser.email }}</div>
          </div>
          <button
            type="button"
            @click="clearUser"
            class="text-gray-500 hover:text-red-400 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Search input -->
        <div v-else class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher par nom, email ou association..."
            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-100 placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          />
          <div v-if="loadingUsers" class="absolute right-3 top-2.5">
            <LoadingSpinner size="sm" />
          </div>

          <!-- Search results dropdown -->
          <div
            v-if="users.length > 0"
            class="absolute z-10 mt-1 w-full bg-gray-700 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <button
              v-for="user in users"
              :key="user.id"
              type="button"
              @click="selectUser(user)"
              class="w-full px-4 py-3 text-left hover:bg-gray-600 border-b border-gray-600 last:border-0 transition-colors"
            >
              <div class="font-medium text-gray-100">
                {{ user.first_name }} {{ user.last_name }}
                <span v-if="user.company_name" class="text-teal-300 ml-1">({{ user.company_name }})</span>
              </div>
              <div class="text-sm text-gray-400">{{ user.email }}</div>
            </button>
          </div>
        </div>
      </div>

      <!-- Pack type -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Type de pack <span class="text-red-400">*</span>
        </label>
        <div class="grid grid-cols-2 gap-4">
          <button
            v-for="(packInfo, key) in packTypes"
            :key="key"
            type="button"
            @click="form.pack_type = key"
            class="p-4 border-2 rounded-lg text-left transition"
            :class="form.pack_type === key
              ? 'border-teal-500 bg-teal-900/30'
              : 'border-gray-600 hover:border-gray-500 bg-gray-700/50'"
          >
            <div class="font-medium text-gray-100">{{ packInfo.label }}</div>
            <div class="text-sm text-gray-400">{{ packInfo.sessions }} séances</div>
            <div class="text-lg font-bold text-teal-400 mt-1">{{ packInfo.price }} €</div>
            <div class="text-xs text-gray-500">{{ packInfo.price_per_session }} € / séance</div>
          </button>
        </div>
      </div>

      <!-- Duration type -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Applicable pour
        </label>
        <select
          v-model="form.duration_type"
          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
        >
          <option
            v-for="dtype in durationTypes"
            :key="dtype"
            :value="dtype"
          >
            {{ labels.duration_type[dtype] || dtype }}
          </option>
        </select>
      </div>

      <!-- Custom values -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Nombre de séances
          </label>
          <input
            v-model.number="form.sessions_total"
            type="number"
            min="1"
            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Prix payé (€)
          </label>
          <input
            v-model.number="form.price_paid"
            type="number"
            min="0"
            step="0.01"
            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          />
          <div class="text-xs text-gray-500 mt-1">
            Soit {{ pricePerSession }} € / séance
          </div>
        </div>
      </div>

      <!-- Expiration -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Date d'expiration
        </label>
        <input
          v-model="form.expires_at"
          type="date"
          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-100 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
        />
        <div class="text-xs text-gray-500 mt-1">
          Laisser vide pour pas de limite de temps
        </div>
      </div>

      <!-- Admin notes -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Notes (visible uniquement par les admins)
        </label>
        <textarea
          v-model="form.admin_notes"
          rows="3"
          placeholder="Notes optionnelles..."
          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-gray-100 placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
        ></textarea>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="router.push('/app/prepaid-packs')"
          class="px-4 py-2 bg-gray-700 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-600 transition"
        >
          Annuler
        </button>
        <button
          type="submit"
          :disabled="saving"
          class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <LoadingSpinner v-if="saving" size="sm" />
          Créer le pack
        </button>
      </div>
    </form>
  </div>
</template>
