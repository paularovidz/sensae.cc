import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { personsApi } from '@/services/api'

export const usePersonsStore = defineStore('persons', () => {
  const persons = ref([])
  const currentPerson = ref(null)
  const pagination = ref({ page: 1, limit: 20, total: 0, pages: 0 })
  const loading = ref(false)
  const error = ref(null)

  const personById = computed(() => {
    return (id) => persons.value.find(p => p.id === id)
  })

  async function fetchPersons(params = {}) {
    loading.value = true
    error.value = null

    try {
      const apiParams = {
        page: params.page || 1,
        limit: params.limit || 20
      }
      if (params.search) {
        apiParams.search = params.search
      }
      const response = await personsApi.getAll(apiParams)

      persons.value = response.data.data.persons
      pagination.value = response.data.data.pagination
      return response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchPerson(id) {
    loading.value = true
    error.value = null

    try {
      const response = await personsApi.getById(id)
      currentPerson.value = response.data.data
      return currentPerson.value
    } catch (e) {
      error.value = e.response?.data?.message || 'Personne non trouvée'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createPerson(data) {
    loading.value = true
    error.value = null

    try {
      const response = await personsApi.create(data)
      const newPerson = response.data.data
      persons.value.unshift(newPerson)
      return newPerson
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la création'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updatePerson(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await personsApi.update(id, data)
      const updatedPerson = response.data.data

      const index = persons.value.findIndex(p => p.id === id)
      if (index !== -1) {
        persons.value[index] = updatedPerson
      }

      if (currentPerson.value?.id === id) {
        currentPerson.value = updatedPerson
      }

      return updatedPerson
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la mise à jour'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deletePerson(id) {
    loading.value = true
    error.value = null

    try {
      await personsApi.delete(id)
      persons.value = persons.value.filter(p => p.id !== id)

      if (currentPerson.value?.id === id) {
        currentPerson.value = null
      }
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la suppression'
      throw e
    } finally {
      loading.value = false
    }
  }

  function clearCurrent() {
    currentPerson.value = null
  }

  return {
    persons,
    currentPerson,
    pagination,
    loading,
    error,
    personById,
    fetchPersons,
    fetchPerson,
    createPerson,
    updatePerson,
    deletePerson,
    clearCurrent
  }
})
