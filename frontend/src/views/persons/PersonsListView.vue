<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { usePersonsStore } from '@/stores/persons'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'

const personsStore = usePersonsStore()
const authStore = useAuthStore()

const loading = ref(true)

onMounted(async () => {
  try {
    await personsStore.fetchPersons()
  } finally {
    loading.value = false
  }
})

async function loadPage(page) {
  loading.value = true
  try {
    await personsStore.fetchPersons({ page })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Personnes</h1>
        <p class="text-gray-600 mt-1">Personnes suivies en séances Snoezelen</p>
      </div>
      <RouterLink v-if="authStore.isAdmin" to="/app/persons/new" class="btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nouvelle personne
      </RouterLink>
    </div>

    <LoadingSpinner v-if="loading" size="lg" class="py-12" />

    <template v-else>
      <EmptyState
        v-if="personsStore.persons.length === 0"
        title="Aucune personne"
        description="Aucune personne n'est assignée à votre compte."
        icon="users"
      >
        <RouterLink v-if="authStore.isAdmin" to="/app/persons/new" class="btn-primary mt-4">
          Ajouter une personne
        </RouterLink>
      </EmptyState>

      <div v-else class="card overflow-hidden">
        <table class="table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Âge</th>
              <th>Séances</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="person in personsStore.persons" :key="person.id">
              <td>
                <RouterLink :to="`/app/persons/${person.id}`" class="flex items-center hover:text-primary-600">
                  <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-medium text-sm mr-3">
                    {{ person.first_name[0] }}{{ person.last_name[0] }}
                  </div>
                  <span class="font-medium">{{ person.first_name }} {{ person.last_name }}</span>
                </RouterLink>
              </td>
              <td>{{ person.age ? person.age + ' ans' : '-' }}</td>
              <td>
                <RouterLink :to="`/app/persons/${person.id}`" class="text-primary-600 hover:text-primary-700">
                  Voir les séances
                </RouterLink>
              </td>
              <td class="text-right">
                <RouterLink :to="`/app/sessions/new/${person.id}`" class="btn-primary btn-sm">
                  Nouvelle séance
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="personsStore.pagination.pages > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
          <div class="text-sm text-gray-500">
            {{ personsStore.pagination.total }} personne(s)
          </div>
          <div class="flex space-x-2">
            <button
              v-for="page in personsStore.pagination.pages"
              :key="page"
              @click="loadPage(page)"
              :class="[
                'px-3 py-1 text-sm rounded',
                page === personsStore.pagination.page
                  ? 'bg-primary-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
