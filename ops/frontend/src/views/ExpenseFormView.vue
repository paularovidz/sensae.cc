<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useOpsStore } from '@/stores/ops'
import { expensesApi, vendorMappingsApi } from '@/services/api'
import VendorAutocomplete from '@/components/ui/VendorAutocomplete.vue'

const router = useRouter()
const route = useRoute()
const opsStore = useOpsStore()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)

const form = ref({
  category_id: '',
  description: '',
  amount: '',
  expense_date: new Date().toISOString().split('T')[0],
  payment_method: 'transfer',
  vendor: '',
  invoice_number: '',
  notes: '',
  save_vendor_mapping: false
})

const categoryAutoApplied = ref(false)

function onCategoryChange() {
  categoryAutoApplied.value = false
}

async function loadData() {
  loading.value = true
  try {
    await opsStore.fetchCategories()

    if (isEdit.value) {
      const response = await expensesApi.getById(route.params.id)
      const expense = response.data.data
      form.value = {
        category_id: expense.category_id,
        description: expense.description,
        amount: expense.amount,
        expense_date: expense.expense_date,
        payment_method: expense.payment_method,
        vendor: expense.vendor || '',
        invoice_number: expense.invoice_number || '',
        notes: expense.notes || '',
        save_vendor_mapping: false
      }
    }
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

async function onVendorChange() {
  categoryAutoApplied.value = false
  suggestedCategory.value = null

  if (!form.value.vendor || form.value.vendor.length < 2) {
    return
  }

  try {
    const response = await vendorMappingsApi.suggest(form.value.vendor)
    if (response.data.data) {
      // Auto-apply category
      form.value.category_id = response.data.data.category_id
      categoryAutoApplied.value = true

      // Update vendor name if display name exists
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
      amount: parseFloat(form.value.amount)
    }

    if (isEdit.value) {
      await expensesApi.update(route.params.id, data)
    } else {
      await expensesApi.create(data)
    }

    router.push('/expenses')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement'
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push('/expenses')
}

const paymentMethods = [
  { value: 'transfer', label: 'Virement' },
  { value: 'card', label: 'Carte bancaire' },
  { value: 'cash', label: 'Espèces' },
  { value: 'check', label: 'Chèque' },
  { value: 'direct_debit', label: 'Prélèvement' }
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
        {{ isEdit ? 'Modifier la dépense' : 'Nouvelle dépense' }}
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
          placeholder="Ex: Amazon, IKEA..."
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

      <!-- Description -->
      <div>
        <label for="description">Description *</label>
        <input
          id="description"
          v-model="form.description"
          type="text"
          required
          class="w-full"
          placeholder="Ex: Fournitures bureau"
        />
      </div>

      <!-- Amount & Date -->
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
          <label for="expense_date">Date *</label>
          <input
            id="expense_date"
            v-model="form.expense_date"
            type="date"
            required
            class="w-full"
          />
        </div>
      </div>

      <!-- Payment method -->
      <div>
        <label for="payment_method">Mode de paiement</label>
        <select id="payment_method" v-model="form.payment_method" class="w-full">
          <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
            {{ method.label }}
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

      <!-- Invoice number -->
      <div>
        <label for="invoice_number">N° de facture</label>
        <input
          id="invoice_number"
          v-model="form.invoice_number"
          type="text"
          class="w-full"
          placeholder="Optionnel"
        />
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
