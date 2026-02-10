<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useOpsStore } from '@/stores/ops'
import { expensesApi } from '@/services/api'
import MonthSelector from '@/components/ui/MonthSelector.vue'

const router = useRouter()
const opsStore = useOpsStore()
const loading = ref(true)
const deleting = ref(null)
const categoryFilter = ref('')

async function loadData() {
  loading.value = true
  try {
    await Promise.all([
      opsStore.fetchExpenses(),
      opsStore.fetchCategories()
    ])
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

watch(
  () => [opsStore.currentYear, opsStore.currentMonth],
  () => loadData()
)

const filteredExpenses = computed(() => {
  if (!categoryFilter.value) return opsStore.expenses
  return opsStore.expenses.filter(e => e.category_id === categoryFilter.value)
})

const totalAmount = computed(() => {
  return filteredExpenses.value.reduce((sum, e) => sum + parseFloat(e.amount), 0)
})

function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(value)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR')
}

async function deleteExpense(id) {
  if (!confirm('Supprimer cette dépense ?')) return

  deleting.value = id
  try {
    await expensesApi.delete(id)
    await loadData()
  } finally {
    deleting.value = null
  }
}

function editExpense(id) {
  router.push(`/expenses/${id}/edit`)
}

function createExpense() {
  router.push('/expenses/new')
}

const paymentMethods = {
  cash: 'Espèces',
  card: 'Carte',
  transfer: 'Virement',
  check: 'Chèque',
  direct_debit: 'Prélèvement'
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-white">Dépenses</h1>
      <div class="flex items-center gap-4">
        <MonthSelector @change="loadData" />
        <button @click="createExpense" class="btn btn-primary">
          + Nouvelle dépense
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
      <select v-model="categoryFilter" class="sm:w-64">
        <option value="">Toutes les catégories</option>
        <option v-for="cat in opsStore.categories" :key="cat.id" :value="cat.id">
          {{ cat.name }}
        </option>
      </select>

      <div class="flex-1"></div>

      <div class="text-right">
        <p class="text-sm text-gray-400">Total</p>
        <p class="text-xl font-bold text-white">{{ formatCurrency(totalAmount) }}</p>
      </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Catégorie</th>
              <th>Fournisseur</th>
              <th>Mode</th>
              <th class="text-right">Montant</th>
              <th class="w-20"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center py-8 text-gray-400">
                Chargement...
              </td>
            </tr>
            <tr v-else-if="!filteredExpenses.length">
              <td colspan="7" class="text-center py-8 text-gray-400">
                Aucune dépense ce mois
              </td>
            </tr>
            <tr v-for="expense in filteredExpenses" :key="expense.id">
              <td class="whitespace-nowrap">{{ formatDate(expense.expense_date) }}</td>
              <td>
                <div>{{ expense.description }}</div>
                <div v-if="expense.recurring_expense_id" class="text-xs text-ops-400">
                  Récurrente
                </div>
              </td>
              <td>
                <span
                  class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium"
                  :style="{ backgroundColor: expense.category_color + '20', color: expense.category_color }"
                >
                  <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: expense.category_color }"></span>
                  {{ expense.category_name }}
                </span>
              </td>
              <td>{{ expense.vendor || '-' }}</td>
              <td>{{ paymentMethods[expense.payment_method] || expense.payment_method }}</td>
              <td class="text-right font-medium">{{ formatCurrency(expense.amount) }}</td>
              <td>
                <div class="flex items-center justify-end gap-1">
                  <button
                    @click="editExpense(expense.id)"
                    class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded"
                    title="Modifier"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="deleteExpense(expense.id)"
                    :disabled="deleting === expense.id"
                    class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded"
                    title="Supprimer"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
