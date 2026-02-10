import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const accessToken = ref(null)
  const refreshToken = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const magicLinkSent = ref(false)
  const magicLinkEmail = ref(null)

  const isAuthenticated = computed(() => !!accessToken.value && !!user.value)
  const fullName = computed(() => {
    if (!user.value) return ''
    return `${user.value.first_name} ${user.value.last_name}`
  })

  function initializeFromStorage() {
    const storedToken = localStorage.getItem('ops_access_token')
    const storedRefresh = localStorage.getItem('ops_refresh_token')
    const storedUser = localStorage.getItem('ops_user')

    if (storedToken && storedUser) {
      accessToken.value = storedToken
      refreshToken.value = storedRefresh
      try {
        user.value = JSON.parse(storedUser)
      } catch (e) {
        clearAuth()
      }
    }
  }

  function setAuth(data) {
    accessToken.value = data.access_token
    refreshToken.value = data.refresh_token
    user.value = data.user

    localStorage.setItem('ops_access_token', data.access_token)
    localStorage.setItem('ops_refresh_token', data.refresh_token)
    localStorage.setItem('ops_user', JSON.stringify(data.user))
  }

  function clearAuth() {
    accessToken.value = null
    refreshToken.value = null
    user.value = null
    magicLinkSent.value = false
    magicLinkEmail.value = null

    localStorage.removeItem('ops_access_token')
    localStorage.removeItem('ops_refresh_token')
    localStorage.removeItem('ops_user')
  }

  async function requestMagicLink(email) {
    loading.value = true
    error.value = null
    magicLinkSent.value = false

    try {
      await authApi.requestMagicLink(email)
      magicLinkSent.value = true
      magicLinkEmail.value = email
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors de l\'envoi du lien'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function verifyMagicLink(token) {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.verifyMagicLink(token)
      setAuth(response.data.data)
      router.push('/')
      return response.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Lien invalide ou expire'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (refreshToken.value) {
        await authApi.logout(refreshToken.value)
      }
    } catch (e) {
      // Ignore errors during logout
    } finally {
      clearAuth()
      router.push('/login')
    }
  }

  async function fetchCurrentUser() {
    try {
      const response = await authApi.me()
      user.value = response.data.data
      localStorage.setItem('ops_user', JSON.stringify(user.value))
      return user.value
    } catch (e) {
      if (e.response?.status === 401) {
        clearAuth()
        router.push('/login')
      }
      throw e
    }
  }

  function resetMagicLinkState() {
    magicLinkSent.value = false
    magicLinkEmail.value = null
    error.value = null
  }

  return {
    user,
    accessToken,
    loading,
    error,
    magicLinkSent,
    magicLinkEmail,
    isAuthenticated,
    fullName,
    initializeFromStorage,
    requestMagicLink,
    verifyMagicLink,
    logout,
    fetchCurrentUser,
    clearAuth,
    resetMagicLinkState
  }
})
