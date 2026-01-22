<script setup>
import { ref, watch } from 'vue'
import { personsApi } from '@/services/api'
import { useToastStore } from '@/stores/toast'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AlertMessage from '@/components/ui/AlertMessage.vue'

const toastStore = useToastStore()

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'created'])

const form = ref({
  first_name: '',
  last_name: '',
  birth_date: ''
})

const loading = ref(false)
const error = ref('')

// Reset form when modal opens
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    form.value = {
      first_name: '',
      last_name: '',
      birth_date: ''
    }
    error.value = ''
  }
})

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

  loading.value = true
  try {
    const data = {
      first_name: form.value.first_name.trim(),
      last_name: form.value.last_name.trim()
    }

    if (form.value.birth_date) {
      data.birth_date = form.value.birth_date
    }

    const response = await personsApi.create(data)

    toastStore.success('Personne ajoutee avec succes')
    emit('created', response.data.data)
    close()
  } catch (e) {
    console.error('Error creating person:', e)
    error.value = e.response?.data?.message || 'Erreur lors de la creation de la personne'
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
          <h3 class="text-lg font-semibold text-white">Ajouter une personne</h3>
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
              placeholder="Prenom de la personne"
              :disabled="loading"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nom *</label>
            <input
              v-model="form.last_name"
              type="text"
              class="input-dark w-full"
              placeholder="Nom de la personne"
              :disabled="loading"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Date de naissance</label>
            <input
              v-model="form.birth_date"
              type="date"
              class="input-dark w-full"
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
              Ajouter
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>
