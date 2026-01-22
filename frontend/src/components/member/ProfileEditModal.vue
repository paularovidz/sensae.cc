<script setup>
import { ref, watch } from 'vue'
import { usersApi } from '@/services/api'
import { useToastStore } from '@/stores/toast'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AlertMessage from '@/components/ui/AlertMessage.vue'

const toastStore = useToastStore()

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'updated'])

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
})

const loading = ref(false)
const error = ref('')

// Reset form when modal opens
watch(() => props.modelValue, (isOpen) => {
  if (isOpen && props.user) {
    form.value = {
      first_name: props.user.first_name || '',
      last_name: props.user.last_name || '',
      email: props.user.email || '',
      phone: props.user.phone || ''
    }
    error.value = ''
  }
}, { immediate: true })

function close() {
  emit('update:modelValue', false)
}

async function handleSubmit() {
  error.value = ''

  // Validation
  if (!form.value.first_name.trim()) {
    error.value = 'Le prenom est requis'
    return
  }
  if (!form.value.last_name.trim()) {
    error.value = 'Le nom est requis'
    return
  }
  if (!form.value.email.trim()) {
    error.value = 'L\'email est requis'
    return
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    error.value = 'L\'email n\'est pas valide'
    return
  }

  loading.value = true
  try {
    const response = await usersApi.update(props.user.id, {
      first_name: form.value.first_name.trim(),
      last_name: form.value.last_name.trim(),
      email: form.value.email.trim(),
      phone: form.value.phone?.trim() || null
    })

    toastStore.success('Profil mis a jour avec succes')
    emit('updated', response.data.data)
    close()
  } catch (e) {
    console.error('Error updating profile:', e)
    error.value = e.response?.data?.message || 'Erreur lors de la mise a jour du profil'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-black/70"
        @click="close"
      ></div>

      <!-- Modal -->
      <div class="relative bg-gray-800 rounded-xl shadow-xl w-full max-w-md border border-gray-700">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
          <h3 class="text-lg font-semibold text-white">Modifier mon profil</h3>
          <button
            @click="close"
            class="p-1 text-gray-400 hover:text-white transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <AlertMessage v-if="error" type="error" dismissible @dismiss="error = ''">
            {{ error }}
          </AlertMessage>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Prenom *</label>
            <input
              v-model="form.first_name"
              type="text"
              class="input-dark w-full"
              placeholder="Votre prenom"
              :disabled="loading"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nom *</label>
            <input
              v-model="form.last_name"
              type="text"
              class="input-dark w-full"
              placeholder="Votre nom"
              :disabled="loading"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Email *</label>
            <input
              v-model="form.email"
              type="email"
              class="input-dark w-full"
              placeholder="votre@email.com"
              :disabled="loading"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Telephone</label>
            <input
              v-model="form.phone"
              type="tel"
              class="input-dark w-full"
              placeholder="06 12 34 56 78"
              :disabled="loading"
            />
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm text-gray-300 hover:text-white transition-colors"
              :disabled="loading"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="loading"
            >
              <LoadingSpinner v-if="loading" size="sm" class="mr-2" />
              Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>
