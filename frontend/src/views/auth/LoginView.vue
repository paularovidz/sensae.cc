<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AlertMessage from '@/components/ui/AlertMessage.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()

const email = ref('')
const submitted = ref(false)
const error = ref('')

async function handleSubmit() {
  if (!email.value) {
    error.value = 'Veuillez saisir votre adresse email'
    return
  }

  error.value = ''

  try {
    await authStore.requestMagicLink(email.value)
    submitted.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue'
  }
}
</script>

<template>
  <div class="card p-8">
    <!-- Success state -->
    <div v-if="submitted" class="text-center">
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-900 mb-2">Vérifiez votre boîte mail</h2>
      <p class="text-gray-600 mb-4">
        Si un compte existe pour <strong>{{ email }}</strong>, vous recevrez un lien de connexion dans quelques instants.
      </p>
      <button @click="submitted = false; email = ''" class="text-primary-600 hover:text-primary-700 font-medium">
        Utiliser une autre adresse
      </button>
    </div>

    <!-- Login form -->
    <form v-else @submit.prevent="handleSubmit" class="space-y-6">
      <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Connexion</h2>
        <p class="text-gray-600 mt-1">Recevez un lien de connexion par email</p>
      </div>

      <AlertMessage v-if="error" type="error" dismissible @dismiss="error = ''">
        {{ error }}
      </AlertMessage>

      <div>
        <label for="email" class="label">Adresse email</label>
        <input
          id="email"
          v-model="email"
          type="email"
          class="input"
          placeholder="votre@email.com"
          autocomplete="email"
          required
        />
      </div>

      <button
        type="submit"
        class="btn-primary w-full"
        :disabled="authStore.loading"
      >
        <LoadingSpinner v-if="authStore.loading" size="sm" class="mr-2" />
        <span v-if="!authStore.loading">Recevoir le lien de connexion</span>
        <span v-else>Envoi en cours...</span>
      </button>

      <p class="text-center text-sm text-gray-500">
        Pas de mot de passe requis. Un lien sécurisé vous sera envoyé par email.
      </p>
    </form>
  </div>
</template>
