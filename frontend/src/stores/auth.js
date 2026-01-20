import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi, usersApi } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const accessToken = ref(null)
  const refreshToken = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!accessToken.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const fullName = computed(() => {
    if (!user.value) return ''
    return `${user.value.first_name} ${user.value.last_name}`
  })

  function initializeFromStorage() {
    const storedToken = localStorage.getItem('access_token')
    const storedRefresh = localStorage.getItem('refresh_token')
    const storedUser = localStorage.getItem('user')

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

    localStorage.setItem('access_token', data.access_token)
    localStorage.setItem('refresh_token', data.refresh_token)
    localStorage.setItem('user', JSON.stringify(data.user))
  }

  function clearAuth() {
    accessToken.value = null
    refreshToken.value = null
    user.value = null

    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
    localStorage.removeItem('user')
  }

  async function requestMagicLink(email) {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.requestMagicLink(email)
      return response.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Une erreur est survenue'
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
      return response.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Lien invalide ou expiré'
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
      const response = await usersApi.getMe()
      user.value = response.data.data
      localStorage.setItem('user', JSON.stringify(user.value))
      return user.value
    } catch (e) {
      if (e.response?.status === 401) {
        clearAuth()
        router.push('/login')
      }
      throw e
    }
  }

  return {
    user,
    accessToken,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    fullName,
    initializeFromStorage,
    requestMagicLink,
    verifyMagicLink,
    logout,
    fetchCurrentUser,
    clearAuth
  }
})
