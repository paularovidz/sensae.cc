<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { usePersonsStore } from '@/stores/persons'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'

const personsStore = usePersonsStore()
const authStore = useAuthStore()

const loading = ref(true)
const searchQuery = ref('')
let searchTimeout = null

onMounted(async () => {
  try {
    await personsStore.fetchPersons()
  } finally {
    loading.value = false
  }
})

// Debounced search
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadPage(1)
  }, 300)
})

async function loadPage(page) {
  loading.value = true
  try {
    const params = { page }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    await personsStore.fetchPersons(params)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="relative flex-1 max-w-md">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher par nom..."
          class="w-full pl-10 pr-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        />
        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <RouterLink v-if="authStore.isAdmin" to="/app/persons/new" class="btn-primary whitespace-nowrap">
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
        :title="searchQuery ? 'Aucun résultat' : 'Aucune personne'"
        :description="searchQuery ? 'Aucune personne ne correspond à votre recherche.' : 'Aucune personne n\'est assignée à votre compte.'"
        icon="users"
      >
        <RouterLink v-if="authStore.isAdmin && !searchQuery" to="/app/persons/new" class="btn-primary mt-4">
          Ajouter une personne
        </RouterLink>
      </EmptyState>

      <div v-else class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <table class="w-full text-sm text-left">
          <thead>
            <tr class="bg-gray-800/50">
              <th class="px-4 py-3 font-medium text-gray-400 uppercase tracking-wider text-xs">Nom</th>
              <th class="px-4 py-3 font-medium text-gray-400 uppercase tracking-wider text-xs">Âge</th>
              <th class="px-4 py-3 font-medium text-gray-400 uppercase tracking-wider text-xs">Séances</th>
              <th class="px-4 py-3 font-medium text-gray-400 uppercase tracking-wider text-xs text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="person in personsStore.persons" :key="person.id" class="border-t border-gray-700 hover:bg-gray-700/50">
              <td class="px-4 py-3">
                <RouterLink :to="`/app/persons/${person.id}`" class="flex items-center hover:text-primary-400">
                  <div class="w-8 h-8 rounded-full bg-primary-900/50 flex items-center justify-center text-primary-400 font-medium text-sm mr-3">
                    {{ person.first_name[0] }}{{ person.last_name[0] }}
                  </div>
                  <span class="font-medium text-gray-100">{{ person.first_name }} {{ person.last_name }}</span>
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-gray-300">{{ person.age ? person.age + ' ans' : '-' }}</td>
              <td class="px-4 py-3">
                <RouterLink :to="`/app/persons/${person.id}`" class="text-primary-400 hover:text-primary-300">
                  Voir les séances
                </RouterLink>
              </td>
              <td class="px-4 py-3 text-right">
                <RouterLink :to="`/app/sessions/new/${person.id}`" class="btn-primary btn-sm">
                  Nouvelle séance
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="personsStore.pagination.pages > 1" class="px-4 py-3 border-t border-gray-700 flex items-center justify-between">
          <div class="text-sm text-gray-400">
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
                  : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
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
