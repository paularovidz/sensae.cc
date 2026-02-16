<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { prepaidPacksApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const props = defineProps({
  userId: {
    type: String,
    required: true
  }
})

const loading = ref(true)
const packs = ref([])
const balance = ref({ total_credits: 0 })
const labels = ref({})

onMounted(async () => {
  await loadPacks()
})

async function loadPacks() {
  loading.value = true
  try {
    const response = await prepaidPacksApi.getByUser(props.userId)
    packs.value = response.data.data.packs || []
    balance.value = response.data.data.balance || { total_credits: 0 }
    labels.value = response.data.data.labels || {}
  } catch (e) {
    console.error('Error loading prepaid packs:', e)
  } finally {
    loading.value = false
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
  return { label: 'Actif', class: 'bg-teal-900/50 text-teal-400' }
}
</script>

<template>
  <div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
      <h3 class="font-semibold text-white">Packs prépayés</h3>
      <div class="flex items-center gap-3">
        <div v-if="balance.total_credits > 0" class="text-sm">
          <span class="text-gray-400">Crédits disponibles :</span>
          <span class="ml-1 font-bold text-teal-400">{{ balance.total_credits }}</span>
        </div>
        <RouterLink
          :to="`/app/prepaid-packs/new?user=${userId}`"
          class="text-sm text-teal-400 hover:text-teal-300 transition-colors"
        >
          + Ajouter un pack
        </RouterLink>
      </div>
    </div>

    <div v-if="loading" class="p-6 flex justify-center">
      <LoadingSpinner />
    </div>

    <div v-else-if="packs.length === 0" class="p-6 text-center text-gray-500">
      Aucun pack prépayé pour cet utilisateur
    </div>

    <div v-else class="divide-y divide-gray-700">
      <RouterLink
        v-for="pack in packs"
        :key="pack.id"
        :to="`/app/prepaid-packs/${pack.id}`"
        class="block px-6 py-4 hover:bg-gray-700/50 transition-colors"
      >
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-medium text-white">
                {{ labels.pack_type?.[pack.pack_type] || pack.pack_type }}
              </span>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                :class="getPackStatus(pack).class"
              >
                {{ getPackStatus(pack).label }}
              </span>
            </div>
            <div class="text-sm text-gray-400 mt-1">
              {{ formatPrice(pack.price_paid) }} -
              {{ labels.duration_type?.[pack.duration_type] || pack.duration_type }}
            </div>
          </div>

          <div class="text-right">
            <div class="text-lg font-bold" :class="pack.sessions_remaining > 0 ? 'text-teal-400' : 'text-gray-500'">
              {{ pack.sessions_remaining }}/{{ pack.sessions_total }}
            </div>
            <div class="text-xs text-gray-500">
              {{ pack.expires_at ? `Expire le ${formatDate(pack.expires_at)}` : 'Sans limite' }}
            </div>
          </div>
        </div>
      </RouterLink>
    </div>
  </div>
</template>
