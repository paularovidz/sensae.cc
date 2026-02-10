<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useOpsStore } from '@/stores/ops'
import { recurringExpensesApi, vendorMappingsApi } from '@/services/api'
import VendorAutocomplete from '@/components/ui/VendorAutocomplete.vue'

const router = useRouter()
const route = useRoute()
const opsStore = useOpsStore()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const categoryAutoApplied = ref(false)

function onCategoryChange() {
  categoryAutoApplied.value = false
}

const form = ref({
  category_id: '',
  description: '',
  amount: '',
  frequency: 'monthly',
  day_of_month: 1,
  vendor: '',
  notes: '',
  is_active: true,
  start_date: new Date().toISOString().split('T')[0],
  end_date: '',
  save_vendor_mapping: false
})

async function loadData() {
  loading.value = true
  try {
    await opsStore.fetchCategories()

    if (isEdit.value) {
      const response = await recurringExpensesApi.getById(route.params.id)
      const expense = response.data.data
      form.value = {
        category_id: expense.category_id,
        description: expense.description,
        amount: expense.amount,
        frequency: expense.frequency,
        day_of_month: expense.day_of_month,
        vendor: expense.vendor || '',
        notes: expense.notes || '',
        is_active: !!expense.is_active,
        start_date: expense.start_date,
        end_date: expense.end_date || ''
      }
    }
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

async function onVendorChange() {
  categoryAutoApplied.value = false

  if (!form.value.vendor || form.value.vendor.length < 2) {
    return
  }

  try {
    const response = await vendorMappingsApi.suggest(form.value.vendor)
    if (response.data.data) {
      form.value.category_id = response.data.data.category_id
      categoryAutoApplied.value = true

      if (response.data.data.vendor_display_name) {
        form.value.vendor = response.data.data.vendor_display_name
      }
    }
  } catch (e) {
    // Ignore errors
  }
}

async function handleSubmit() {
  saving.value = true
  error.value = null

  try {
    const data = {
      ...form.value,
      amount: parseFloat(form.value.amount),
      day_of_month: parseInt(form.value.day_of_month),
      is_active: form.value.is_active ? 1 : 0,
      end_date: form.value.end_date || null
    }

    if (isEdit.value) {
      await recurringExpensesApi.update(route.params.id, data)
    } else {
      await recurringExpensesApi.create(data)
    }

    router.push('/recurring')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement'
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push('/recurring')
}

const frequencies = [
  { value: 'monthly', label: 'Mensuelle' },
  { value: 'quarterly', label: 'Trimestrielle' },
  { value: 'yearly', label: 'Annuelle' }
]
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
      <button @click="cancel" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold text-white">
        {{ isEdit ? 'Modifier la dépense récurrente' : 'Nouvelle dépense récurrente' }}
      </h1>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="card space-y-6">
      <!-- Error -->
      <div v-if="error" class="p-3 rounded-lg bg-red-900/50 border border-red-700 text-red-400 text-sm">
        {{ error }}
      </div>

      <!-- Vendor -->
      <div>
        <label for="vendor">Fournisseur</label>
        <VendorAutocomplete
          v-model="form.vendor"
          placeholder="Ex: Orange, EDF..."
          @select="onVendorChange"
          @blur="onVendorChange"
        />
      </div>

      <!-- Category -->
      <div>
        <div class="flex items-center gap-2">
          <label for="category_id">Catégorie *</label>
          <span v-if="categoryAutoApplied" class="text-xs text-ops-400">(auto)</span>
        </div>
        <select id="category_id" v-model="form.category_id" required class="w-full" @change="onCategoryChange">
          <option value="">Sélectionner une catégorie</option>
          <option v-for="cat in opsStore.categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>

      <!-- Save vendor mapping -->
      <div v-if="form.vendor" class="flex items-center gap-2">
        <input
          id="save_vendor_mapping"
          v-model="form.save_vendor_mapping"
          type="checkbox"
          class="w-4 h-4 text-ops-600 bg-gray-700 border-gray-600 rounded focus:ring-ops-500"
        />
        <label for="save_vendor_mapping" class="text-sm text-gray-300">
          Mémoriser cette catégorie pour ce fournisseur
        </label>
      </div>

      <!-- Description -->
      <div>
        <label for="description">Description *</label>
        <input
          id="description"
          v-model="form.description"
          type="text"
          required
          class="w-full"
          placeholder="Ex: Loyer local"
        />
      </div>

      <!-- Amount & Frequency -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="amount">Montant (€) *</label>
          <input
            id="amount"
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full"
            placeholder="0.00"
          />
        </div>
        <div>
          <label for="frequency">Fréquence *</label>
          <select id="frequency" v-model="form.frequency" class="w-full">
            <option v-for="freq in frequencies" :key="freq.value" :value="freq.value">
              {{ freq.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Day of month -->
      <div>
        <label for="day_of_month">Jour du mois</label>
        <input
          id="day_of_month"
          v-model="form.day_of_month"
          type="number"
          min="1"
          max="31"
          class="w-full"
        />
        <p class="text-xs text-gray-500 mt-1">Jour du mois où la dépense est prélevée (1-31)</p>
      </div>

      <!-- Date range -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="start_date">Date de début *</label>
          <input
            id="start_date"
            v-model="form.start_date"
            type="date"
            required
            class="w-full"
          />
        </div>
        <div>
          <label for="end_date">Date de fin</label>
          <input
            id="end_date"
            v-model="form.end_date"
            type="date"
            class="w-full"
          />
          <p class="text-xs text-gray-500 mt-1">Laisser vide pour récurrence indéfinie</p>
        </div>
      </div>

      <!-- Active -->
      <div class="flex items-center gap-2">
        <input
          id="is_active"
          v-model="form.is_active"
          type="checkbox"
          class="w-4 h-4 text-ops-600 bg-gray-700 border-gray-600 rounded focus:ring-ops-500"
        />
        <label for="is_active" class="text-sm text-gray-300">
          Dépense active
        </label>
      </div>

      <!-- Notes -->
      <div>
        <label for="notes">Notes</label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="3"
          class="w-full"
          placeholder="Notes additionnelles..."
        />
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-4 pt-4 border-t border-gray-700">
        <button type="submit" :disabled="saving" class="btn btn-primary">
          {{ saving ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer') }}
        </button>
        <button type="button" @click="cancel" class="btn btn-secondary">
          Annuler
        </button>
      </div>
    </form>
  </div>
</template>
