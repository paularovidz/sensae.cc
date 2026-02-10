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
    const token = localStorage.getItem('ops_access_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Token refresh state management
let isRefreshing = false
let failedQueue = []

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error)
    } else {
      prom.resolve(token)
    }
  })
  failedQueue = []
}

// Response interceptor - handle token refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    if (error.response?.status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject })
        }).then(token => {
          originalRequest.headers.Authorization = `Bearer ${token}`
          return api(originalRequest)
        }).catch(err => {
          return Promise.reject(err)
        })
      }

      originalRequest._retry = true
      isRefreshing = true

      const refreshToken = localStorage.getItem('ops_refresh_token')

      if (refreshToken) {
        try {
          const response = await axios.post(`${API_URL}/auth/refresh`, {
            refresh_token: refreshToken
          })

          const { access_token, refresh_token } = response.data.data

          localStorage.setItem('ops_access_token', access_token)
          localStorage.setItem('ops_refresh_token', refresh_token)

          processQueue(null, access_token)

          originalRequest.headers.Authorization = `Bearer ${access_token}`
          return api(originalRequest)
        } catch (refreshError) {
          processQueue(refreshError, null)
          localStorage.removeItem('ops_access_token')
          localStorage.removeItem('ops_refresh_token')
          localStorage.removeItem('ops_user')
          window.location.href = '/login'
          return Promise.reject(refreshError)
        } finally {
          isRefreshing = false
        }
      } else {
        isRefreshing = false
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
  logout: (refreshToken) => api.post('/auth/logout', { refresh_token: refreshToken }),
  me: () => api.get('/auth/me')
}

// Dashboard API
export const dashboardApi = {
  get: (year, month) => api.get('/dashboard', { params: { year, month } }),
  getYear: (year) => api.get('/dashboard/year', { params: { year } }),
  getDaily: (year, month) => api.get('/dashboard/daily', { params: { year, month } }),
  health: () => api.get('/dashboard/health')
}

// Categories API
export const categoriesApi = {
  getAll: (params) => api.get('/categories', { params }),
  getById: (id) => api.get(`/categories/${id}`),
  create: (data) => api.post('/categories', data),
  update: (id, data) => api.put(`/categories/${id}`, data),
  delete: (id) => api.delete(`/categories/${id}`)
}

// Expenses API
export const expensesApi = {
  getAll: (params) => api.get('/expenses', { params }),
  getById: (id) => api.get(`/expenses/${id}`),
  getByCategory: (year, month) => api.get('/expenses/by-category', { params: { year, month } }),
  getMonthlyTotals: (year) => api.get('/expenses/monthly-totals', { params: { year } }),
  create: (data) => api.post('/expenses', data),
  update: (id, data) => api.put(`/expenses/${id}`, data),
  delete: (id) => api.delete(`/expenses/${id}`)
}

// Recurring Expenses API
export const recurringExpensesApi = {
  getAll: (params) => api.get('/recurring-expenses', { params }),
  getById: (id) => api.get(`/recurring-expenses/${id}`),
  getMonthlyTotal: () => api.get('/recurring-expenses/monthly-total'),
  create: (data) => api.post('/recurring-expenses', data),
  update: (id, data) => api.put(`/recurring-expenses/${id}`, data),
  delete: (id) => api.delete(`/recurring-expenses/${id}`),
  generate: (year, month) => api.post('/recurring-expenses/generate', { year, month }),
  generateYear: (year) => api.post('/recurring-expenses/generate-year', { year })
}

// Vendor Mappings API
export const vendorMappingsApi = {
  getAll: () => api.get('/vendor-mappings'),
  getById: (id) => api.get(`/vendor-mappings/${id}`),
  suggest: (vendor) => api.get('/vendor-mappings/suggest', { params: { vendor } }),
  search: (query, limit = 10) => api.get('/vendor-mappings/search', { params: { q: query, limit } }),
  create: (data) => api.post('/vendor-mappings', data),
  update: (id, data) => api.put(`/vendor-mappings/${id}`, data),
  delete: (id) => api.delete(`/vendor-mappings/${id}`)
}

// Import API
export const importApi = {
  history: (limit) => api.get('/imports', { params: { limit } }),
  preview: (formData) => api.post('/imports/preview', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  import: (formData) => api.post('/imports', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}
