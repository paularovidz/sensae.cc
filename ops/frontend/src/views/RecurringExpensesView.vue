<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useOpsStore } from '@/stores/ops'
import { recurringExpensesApi } from '@/services/api'

const router = useRouter()
const opsStore = useOpsStore()
const loading = ref(true)
const generating = ref(false)
const generatingYear = ref(false)
const deleting = ref(null)

async function loadData() {
  loading.value = true
  try {
    await opsStore.fetchRecurringExpenses()
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(value)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

const frequencies = {
  monthly: 'Mensuelle',
  quarterly: 'Trimestrielle',
  yearly: 'Annuelle'
}

async function deleteRecurring(id) {
  if (!confirm('Supprimer cette dépense récurrente ?')) return

  deleting.value = id
  try {
    await recurringExpensesApi.delete(id)
    await loadData()
  } finally {
    deleting.value = null
  }
}

function editRecurring(id) {
  router.push(`/recurring/${id}/edit`)
}

function createRecurring() {
  router.push('/recurring/new')
}

async function generateExpenses() {
  if (!confirm(`Générer les dépenses récurrentes pour ${opsStore.monthLabel} ${opsStore.currentYear} ?`)) return

  generating.value = true
  try {
    const result = await opsStore.generateRecurringExpenses()
    alert(`${result.generated_count} dépense(s) générée(s)`)
  } catch (e) {
    alert('Erreur lors de la génération')
  } finally {
    generating.value = false
  }
}

async function generateYearExpenses() {
  if (!confirm(`Générer les dépenses récurrentes pour toute l'année ${opsStore.currentYear} ?`)) return

  generatingYear.value = true
  try {
    const result = await recurringExpensesApi.generateYear(opsStore.currentYear)
    alert(`${result.data.data.generated_count} dépense(s) générée(s) pour ${opsStore.currentYear}`)
  } catch (e) {
    alert('Erreur lors de la génération')
  } finally {
    generatingYear.value = false
  }
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Dépenses récurrentes</h1>
        <p class="text-gray-400 mt-1">Charges fixes mensuelles, trimestrielles ou annuelles</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="generateExpenses" :disabled="generating || generatingYear" class="btn btn-secondary">
          {{ generating ? 'Génération...' : 'Générer pour ce mois' }}
        </button>
        <button @click="generateYearExpenses" :disabled="generating || generatingYear" class="btn btn-secondary">
          {{ generatingYear ? 'Génération...' : 'Générer pour l\'année' }}
        </button>
        <button @click="createRecurring" class="btn btn-primary">
          + Nouvelle récurrente
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table>
          <thead>
            <tr>
              <th>Description</th>
              <th>Catégorie</th>
              <th>Fournisseur</th>
              <th>Fréquence</th>
              <th>Jour</th>
              <th class="text-right">Montant</th>
              <th>Début</th>
              <th>Fin</th>
              <th>Statut</th>
              <th class="w-20"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="10" class="text-center py-8 text-gray-400">
                Chargement...
              </td>
            </tr>
            <tr v-else-if="!opsStore.recurringExpenses.length">
              <td colspan="10" class="text-center py-8 text-gray-400">
                Aucune dépense récurrente
              </td>
            </tr>
            <tr v-for="expense in opsStore.recurringExpenses" :key="expense.id">
              <td>{{ expense.description }}</td>
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
              <td>{{ frequencies[expense.frequency] }}</td>
              <td>{{ expense.day_of_month }}</td>
              <td class="text-right font-medium">{{ formatCurrency(expense.amount) }}</td>
              <td>{{ formatDate(expense.start_date) }}</td>
              <td>{{ formatDate(expense.end_date) }}</td>
              <td>
                <span :class="[
                  'badge',
                  expense.is_active ? 'badge-success' : 'badge-danger'
                ]">
                  {{ expense.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <div class="flex items-center justify-end gap-1">
                  <button
                    @click="editRecurring(expense.id)"
                    class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded"
                    title="Modifier"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="deleteRecurring(expense.id)"
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
