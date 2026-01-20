import { defineStore } from 'pinia'
import { ref } from 'vue'
import { sessionsApi } from '@/services/api'

export const useSessionsStore = defineStore('sessions', () => {
  const sessions = ref([])
  const currentSession = ref(null)
  const labels = ref(null)
  const pagination = ref({ page: 1, limit: 20, total: 0, pages: 0 })
  const loading = ref(false)
  const error = ref(null)

  async function fetchSessions(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await sessionsApi.getAll({
        page: params.page || 1,
        limit: params.limit || 20
      })

      sessions.value = response.data.data.sessions
      pagination.value = response.data.data.pagination
      return response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchSession(id) {
    loading.value = true
    error.value = null

    try {
      const response = await sessionsApi.getById(id)
      currentSession.value = response.data.data
      return currentSession.value
    } catch (e) {
      error.value = e.response?.data?.message || 'Séance non trouvée'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchLabels() {
    if (labels.value) return labels.value

    try {
      const response = await sessionsApi.getLabels()
      labels.value = response.data.data
      return labels.value
    } catch (e) {
      console.error('Error fetching labels:', e)
      return null
    }
  }

  async function createSession(data) {
    loading.value = true
    error.value = null

    try {
      const response = await sessionsApi.create(data)
      const newSession = response.data.data
      sessions.value.unshift(newSession)
      return newSession
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la création'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateSession(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await sessionsApi.update(id, data)
      const updatedSession = response.data.data

      const index = sessions.value.findIndex(s => s.id === id)
      if (index !== -1) {
        sessions.value[index] = updatedSession
      }

      if (currentSession.value?.id === id) {
        currentSession.value = updatedSession
      }

      return updatedSession
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la mise à jour'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteSession(id) {
    loading.value = true
    error.value = null

    try {
      await sessionsApi.delete(id)
      sessions.value = sessions.value.filter(s => s.id !== id)

      if (currentSession.value?.id === id) {
        currentSession.value = null
      }
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la suppression'
      throw e
    } finally {
      loading.value = false
    }
  }

  function clearCurrent() {
    currentSession.value = null
  }

  return {
    sessions,
    currentSession,
    labels,
    pagination,
    loading,
    error,
    fetchSessions,
    fetchSession,
    fetchLabels,
    createSession,
    updateSession,
    deleteSession,
    clearCurrent
  }
})
