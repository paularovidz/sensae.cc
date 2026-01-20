<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePersonsStore } from '@/stores/persons'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AlertMessage from '@/components/ui/AlertMessage.vue'

const route = useRoute()
const router = useRouter()
const personsStore = usePersonsStore()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const error = ref('')

const form = ref({
  first_name: '',
  last_name: '',
  birth_date: '',
  notes: '',
  sessions_per_month: null
})

onMounted(async () => {
  if (isEdit.value) {
    loading.value = true
    try {
      const person = await personsStore.fetchPerson(route.params.id)
      form.value = {
        first_name: person.first_name || '',
        last_name: person.last_name || '',
        birth_date: person.birth_date || '',
        notes: person.notes || '',
        sessions_per_month: person.sessions_per_month || null
      }
    } catch (e) {
      router.push('/app/persons')
    } finally {
      loading.value = false
    }
  }
})

async function handleSubmit() {
  error.value = ''
  saving.value = true

  try {
    if (isEdit.value) {
      await personsStore.updatePerson(route.params.id, form.value)
      router.push(`/app/persons/${route.params.id}`)
    } else {
      const person = await personsStore.createPerson(form.value)
      router.push(`/app/persons/${person.id}`)
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue'

    if (e.response?.data?.errors) {
      const errors = e.response.data.errors
      error.value = Object.values(errors).join(', ')
    }
  } finally {
    saving.value = false
  }
}

function cancel() {
  if (isEdit.value) {
    router.push(`/app/persons/${route.params.id}`)
  } else {
    router.push('/app/persons')
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ isEdit ? 'Modifier la personne' : 'Nouvelle personne' }}
      </h1>
    </div>

    <LoadingSpinner v-if="loading" size="lg" class="py-12" />

    <form v-else @submit.prevent="handleSubmit" class="card">
      <div class="card-body space-y-6">
        <AlertMessage v-if="error" type="error" dismissible @dismiss="error = ''">
          {{ error }}
        </AlertMessage>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="first_name" class="label">Prénom *</label>
            <input
              id="first_name"
              v-model="form.first_name"
              type="text"
              class="input"
              required
            />
          </div>

          <div>
            <label for="last_name" class="label">Nom *</label>
            <input
              id="last_name"
              v-model="form.last_name"
              type="text"
              class="input"
              required
            />
          </div>
        </div>

        <div>
          <label for="birth_date" class="label">Date de naissance</label>
          <input
            id="birth_date"
            v-model="form.birth_date"
            type="date"
            class="input max-w-xs"
          />
        </div>

        <div>
          <label for="sessions_per_month" class="label">Séances par mois</label>
          <input
            id="sessions_per_month"
            v-model.number="form.sessions_per_month"
            type="number"
            min="1"
            class="input max-w-xs"
            placeholder="Ex: 4"
          />
        </div>

        <div>
          <label for="notes" class="label">Notes</label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="4"
            class="input"
            placeholder="Notes générales sur la personne..."
          ></textarea>
          <p class="mt-1 text-sm text-gray-500">Ces notes sont chiffrées et confidentielles.</p>
        </div>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3 rounded-b-xl">
        <button type="button" @click="cancel" class="btn-secondary">
          Annuler
        </button>
        <button type="submit" class="btn-primary" :disabled="saving">
          <LoadingSpinner v-if="saving" size="sm" class="mr-2" />
          {{ isEdit ? 'Enregistrer' : 'Créer' }}
        </button>
      </div>
    </form>
  </div>
</template>
