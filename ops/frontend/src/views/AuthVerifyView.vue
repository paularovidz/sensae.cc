<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  const token = route.params.token

  if (!token) {
    error.value = 'Token manquant'
    loading.value = false
    return
  }

  try {
    await authStore.verifyMagicLink(token)
    // Success - router.push('/') is called in the store
  } catch (e) {
    error.value = e.response?.data?.message || 'Lien invalide ou expire'
    loading.value = false
  }
})

function goToLogin() {
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900 px-4">
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-ops-400">OPS</h1>
        <p class="mt-2 text-gray-400">Cockpit Financier sensae</p>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="card text-center">
        <div class="mx-auto w-16 h-16 bg-ops-900/50 rounded-full flex items-center justify-center mb-4">
          <svg class="animate-spin h-8 w-8 text-ops-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-white mb-2">Verification en cours...</h2>
        <p class="text-gray-400">Veuillez patienter</p>
      </div>

      <!-- Error state -->
      <div v-else class="card text-center">
        <div class="mx-auto w-16 h-16 bg-red-900/50 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-white mb-2">Lien invalide</h2>
        <p class="text-gray-400 mb-6">{{ error }}</p>
        <button
          @click="goToLogin"
          class="btn btn-primary"
        >
          Retour a la connexion
        </button>
      </div>
    </div>
  </div>
</template>
