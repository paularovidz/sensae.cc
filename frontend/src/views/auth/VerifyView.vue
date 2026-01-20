<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AlertMessage from '@/components/ui/AlertMessage.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const verifying = ref(true)
const error = ref('')

onMounted(async () => {
  const token = route.params.token

  if (!token) {
    error.value = 'Token manquant'
    verifying.value = false
    return
  }

  try {
    await authStore.verifyMagicLink(token)

    // Redirect to dashboard or requested page
    const redirect = route.query.redirect || '/app/dashboard'
    router.push(redirect)
  } catch (e) {
    error.value = e.response?.data?.message || 'Lien invalide ou expiré'
    verifying.value = false
  }
})
</script>

<template>
  <div class="card p-8">
    <!-- Verifying -->
    <div v-if="verifying" class="text-center py-8">
      <LoadingSpinner size="lg" class="mb-4" />
      <h2 class="text-xl font-semibold text-gray-900">Vérification en cours...</h2>
      <p class="text-gray-600 mt-2">Veuillez patienter</p>
    </div>

    <!-- Error -->
    <div v-else class="text-center">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>

      <h2 class="text-xl font-semibold text-gray-900 mb-2">Échec de la vérification</h2>

      <AlertMessage type="error" class="mb-6">
        {{ error }}
      </AlertMessage>

      <p class="text-gray-600 mb-4">
        Le lien de connexion est peut-être expiré ou a déjà été utilisé.
      </p>

      <RouterLink to="/login" class="btn-primary">
        Demander un nouveau lien
      </RouterLink>
    </div>
  </div>
</template>
