import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { sensoryProposalsApi } from '@/services/api'

export const useProposalsStore = defineStore('proposals', () => {
  const proposals = ref([])
  const types = ref(null)
  const typeLabels = ref({})
  const pagination = ref({ page: 1, limit: 50, total: 0, pages: 0 })
  const loading = ref(false)
  const error = ref(null)

  const proposalsByType = computed(() => {
    const grouped = {}
    for (const proposal of proposals.value) {
      if (!grouped[proposal.type]) {
        grouped[proposal.type] = []
      }
      grouped[proposal.type].push(proposal)
    }
    return grouped
  })

  async function fetchProposals(params = {}) {
    loading.value = true
    error.value = null

    try {
      const apiParams = {
        page: params.page || 1,
        limit: params.limit || 50
      }
      if (params.type) {
        apiParams.type = params.type
      }
      if (params.search) {
        apiParams.search = params.search
      }
      const response = await sensoryProposalsApi.getAll(apiParams)

      proposals.value = response.data.data.proposals
      typeLabels.value = response.data.data.types || {}
      pagination.value = response.data.data.pagination
      return response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchTypes() {
    if (types.value) return { types: types.value, labels: typeLabels.value }

    try {
      const response = await sensoryProposalsApi.getTypes()
      types.value = response.data.data.types
      typeLabels.value = response.data.data.labels
      return response.data.data
    } catch (e) {
      console.error('Error fetching types:', e)
      return null
    }
  }

  async function searchProposals(query, type = null, limit = 20) {
    if (!query || query.length < 2) return []

    try {
      const response = await sensoryProposalsApi.search({ q: query, type, limit })
      return response.data.data.proposals
    } catch (e) {
      console.error('Error searching proposals:', e)
      return []
    }
  }

  async function createProposal(data) {
    loading.value = true
    error.value = null

    try {
      const response = await sensoryProposalsApi.create(data)
      const newProposal = response.data.data
      proposals.value.unshift(newProposal)
      return newProposal
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la création'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateProposal(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await sensoryProposalsApi.update(id, data)
      const updatedProposal = response.data.data

      const index = proposals.value.findIndex(p => p.id === id)
      if (index !== -1) {
        proposals.value[index] = updatedProposal
      }

      return updatedProposal
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la mise à jour'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteProposal(id) {
    loading.value = true
    error.value = null

    try {
      await sensoryProposalsApi.delete(id)
      proposals.value = proposals.value.filter(p => p.id !== id)
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de la suppression'
      throw e
    } finally {
      loading.value = false
    }
  }

  function getTypeLabel(type) {
    return typeLabels.value[type] || type
  }

  function getTypeBadgeClass(type) {
    const classes = {
      tactile: 'badge-tactile',
      visual: 'badge-visual',
      olfactory: 'badge-olfactory',
      gustatory: 'badge-gustatory',
      auditory: 'badge-auditory',
      proprioceptive: 'badge-proprioceptive',
      vestibular: 'badge-vestibular'
    }
    return classes[type] || 'badge-gray'
  }

  return {
    proposals,
    types,
    typeLabels,
    pagination,
    loading,
    error,
    proposalsByType,
    fetchProposals,
    fetchTypes,
    searchProposals,
    createProposal,
    updateProposal,
    deleteProposal,
    getTypeLabel,
    getTypeBadgeClass
  }
})
