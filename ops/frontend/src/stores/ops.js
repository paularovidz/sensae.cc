import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  dashboardApi,
  categoriesApi,
  expensesApi,
  recurringExpensesApi
} from '@/services/api'

export const useOpsStore = defineStore('ops', () => {
  // Current period
  const currentYear = ref(new Date().getFullYear())
  const currentMonth = ref(new Date().getMonth() + 1)

  // View mode: 'annual' or 'monthly'
  const viewMode = ref('annual')

  // Data
  const dashboard = ref(null)
  const yearData = ref(null)
  const dailyData = ref(null)
  const categories = ref([])
  const expenses = ref([])
  const recurringExpenses = ref([])

  // Loading states
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const monthLabel = computed(() => {
    const months = [
      'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
      'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ]
    return months[currentMonth.value - 1]
  })

  const isAnnualView = computed(() => viewMode.value === 'annual')
  const isMonthlyView = computed(() => viewMode.value === 'monthly')

  // Actions
  async function fetchDashboard() {
    loading.value = true
    error.value = null
    try {
      const response = await dashboardApi.get(currentYear.value, currentMonth.value)
      dashboard.value = response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchYearData() {
    loading.value = true
    error.value = null
    try {
      const response = await dashboardApi.getYear(currentYear.value)
      yearData.value = response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchDailyData() {
    loading.value = true
    error.value = null
    try {
      const response = await dashboardApi.getDaily(currentYear.value, currentMonth.value)
      dailyData.value = response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur lors du chargement'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories(withStats = false) {
    try {
      const params = withStats
        ? { with_stats: true, year: currentYear.value, month: currentMonth.value }
        : {}
      const response = await categoriesApi.getAll(params)
      categories.value = response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur'
      throw e
    }
  }

  async function fetchExpenses(filters = {}) {
    loading.value = true
    try {
      const params = {
        year: currentYear.value,
        month: currentMonth.value,
        ...filters
      }
      const response = await expensesApi.getAll(params)
      expenses.value = response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchRecurringExpenses() {
    loading.value = true
    try {
      const response = await recurringExpensesApi.getAll()
      recurringExpenses.value = response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur'
      throw e
    } finally {
      loading.value = false
    }
  }

  function setViewMode(mode) {
    viewMode.value = mode
  }

  async function generateRecurringExpenses() {
    try {
      const response = await recurringExpensesApi.generate(currentYear.value, currentMonth.value)
      await fetchExpenses()
      await fetchDashboard()
      return response.data.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Erreur'
      throw e
    }
  }

  function setMonth(year, month) {
    currentYear.value = year
    currentMonth.value = month
  }

  function nextMonth() {
    if (currentMonth.value === 12) {
      currentMonth.value = 1
      currentYear.value++
    } else {
      currentMonth.value++
    }
  }

  function prevMonth() {
    if (currentMonth.value === 1) {
      currentMonth.value = 12
      currentYear.value--
    } else {
      currentMonth.value--
    }
  }

  return {
    // State
    currentYear,
    currentMonth,
    viewMode,
    dashboard,
    yearData,
    dailyData,
    categories,
    expenses,
    recurringExpenses,
    loading,
    error,

    // Computed
    monthLabel,
    isAnnualView,
    isMonthlyView,

    // Actions
    fetchDashboard,
    fetchYearData,
    fetchDailyData,
    fetchCategories,
    fetchExpenses,
    fetchRecurringExpenses,
    generateRecurringExpenses,
    setViewMode,
    setMonth,
    nextMonth,
    prevMonth
  }
})
