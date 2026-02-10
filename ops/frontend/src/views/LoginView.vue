<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const email = ref('bonjour@sensea.cc')
const loading = ref(false)
const error = ref(null)

onMounted(() => {
  authStore.resetMagicLinkState()
})

async function handleSubmit() {
  loading.value = true
  error.value = null

  try {
    await authStore.requestMagicLink(email.value)
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'envoi du lien'
  } finally {
    loading.value = false
  }
}

function resetForm() {
  authStore.resetMagicLinkState()
  email.value = 'bonjour@sensea.cc'
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900 px-4">
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-ops-400">OPS</h1>
        <p class="mt-2 text-gray-400">Cockpit Financier sensea</p>
      </div>

      <!-- Magic link sent confirmation -->
      <div v-if="authStore.magicLinkSent" class="card">
        <div class="text-center">
          <div class="mx-auto w-16 h-16 bg-green-900/50 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-white mb-2">Verifiez votre email</h2>
          <p class="text-gray-400 mb-4">
            Un lien de connexion a ete envoye a <span class="text-ops-400 font-medium">{{ authStore.magicLinkEmail }}</span>
          </p>
          <p class="text-sm text-gray-500 mb-6">
            Ce lien est valable pendant 15 minutes et ne peut etre utilise qu'une seule fois.
          </p>
          <button
            @click="resetForm"
            class="text-ops-400 hover:text-ops-300 text-sm font-medium"
          >
            Utiliser une autre adresse email
          </button>
        </div>
      </div>

      <!-- Login form -->
      <div v-else class="card">
        <h2 class="text-xl font-semibold text-white mb-6">Connexion</h2>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Error message -->
          <div v-if="error || authStore.error" class="p-3 rounded-lg bg-red-900/50 border border-red-700 text-red-400 text-sm">
            {{ error || authStore.error }}
          </div>

          <!-- Email -->
          <div>
            <label for="email">Email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="w-full"
              placeholder="bonjour@sensea.cc"
              :disabled="loading"
            />
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="btn btn-primary w-full"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Envoi en cours...
            </span>
            <span v-else>Recevoir un lien de connexion</span>
          </button>
        </form>
      </div>

      <!-- Info -->
      <p class="mt-6 text-center text-sm text-gray-500">
        Connexion securisee par lien magique
      </p>
    </div>
  </div>
</template>
