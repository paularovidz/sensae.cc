import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || '/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Request interceptor - add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('access_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor - handle token refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    // If 401 and not already retrying
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true

      const refreshToken = localStorage.getItem('refresh_token')

      if (refreshToken) {
        try {
          const response = await axios.post(`${API_URL}/auth/refresh`, {
            refresh_token: refreshToken
          })

          const { access_token, refresh_token } = response.data.data

          localStorage.setItem('access_token', access_token)
          localStorage.setItem('refresh_token', refresh_token)

          originalRequest.headers.Authorization = `Bearer ${access_token}`
          return api(originalRequest)
        } catch (refreshError) {
          // Refresh failed, clear tokens and redirect to login
          localStorage.removeItem('access_token')
          localStorage.removeItem('refresh_token')
          localStorage.removeItem('user')
          window.location.href = '/login'
          return Promise.reject(refreshError)
        }
      } else {
        // No refresh token, redirect to login
        window.location.href = '/login'
      }
    }

    return Promise.reject(error)
  }
)

export default api

// Auth API
export const authApi = {
  requestMagicLink: (email) => api.post('/auth/request-magic-link', { email }),
  verifyMagicLink: (token) => api.get(`/auth/verify/${token}`),
  refresh: (refreshToken) => api.post('/auth/refresh', { refresh_token: refreshToken }),
  logout: (refreshToken) => api.post('/auth/logout', { refresh_token: refreshToken })
}

// Users API
export const usersApi = {
  getAll: (params) => api.get('/users', { params }),
  getMe: () => api.get('/users/me'),
  getById: (id) => api.get(`/users/${id}`),
  create: (data) => api.post('/users', data),
  update: (id, data) => api.put(`/users/${id}`, data),
  delete: (id) => api.delete(`/users/${id}`),
  assignPerson: (userId, personId) => api.post(`/users/${userId}/persons/${personId}`),
  unassignPerson: (userId, personId) => api.delete(`/users/${userId}/persons/${personId}`)
}

// Persons API
export const personsApi = {
  getAll: (params) => api.get('/persons', { params }),
  getById: (id) => api.get(`/persons/${id}`),
  getSessions: (id, params) => api.get(`/persons/${id}/sessions`, { params }),
  create: (data) => api.post('/persons', data),
  update: (id, data) => api.put(`/persons/${id}`, data),
  delete: (id) => api.delete(`/persons/${id}`)
}

// Sessions API
export const sessionsApi = {
  getAll: (params) => api.get('/sessions', { params }),
  getById: (id) => api.get(`/sessions/${id}`),
  getLabels: () => api.get('/sessions/labels'),
  getStats: (params) => api.get('/sessions/stats', { params }),
  getPersonStats: (personId) => api.get(`/sessions/person/${personId}/stats`),
  create: (data) => api.post('/sessions', data),
  update: (id, data) => api.put(`/sessions/${id}`, data),
  delete: (id) => api.delete(`/sessions/${id}`)
}

// Sensory Proposals API
export const sensoryProposalsApi = {
  getAll: (params) => api.get('/sensory-proposals', { params }),
  getById: (id) => api.get(`/sensory-proposals/${id}`),
  getTypes: () => api.get('/sensory-proposals/types'),
  search: (params) => api.get('/sensory-proposals/search', { params }),
  create: (data) => api.post('/sensory-proposals', data),
  update: (id, data) => api.put(`/sensory-proposals/${id}`, data),
  delete: (id) => api.delete(`/sensory-proposals/${id}`)
}

// Stats API
export const statsApi = {
  getDashboard: () => api.get('/stats/dashboard'),
  getAuditLogs: (params) => api.get('/stats/audit-logs', { params })
}
