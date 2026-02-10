<script setup>
import { onMounted, ref } from 'vue'
import { useOpsStore } from '@/stores/ops'
import { categoriesApi } from '@/services/api'

const opsStore = useOpsStore()
const loading = ref(true)
const saving = ref(false)
const deleting = ref(null)
const showForm = ref(false)
const editingId = ref(null)

const form = ref({
  name: '',
  color: '#6B7280',
  icon: '',
  sort_order: 0
})

async function loadData() {
  loading.value = true
  try {
    await opsStore.fetchCategories(true)
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

function openCreate() {
  editingId.value = null
  form.value = {
    name: '',
    color: '#6B7280',
    icon: '',
    sort_order: opsStore.categories.length
  }
  showForm.value = true
}

function openEdit(category) {
  editingId.value = category.id
  form.value = {
    name: category.name,
    color: category.color,
    icon: category.icon || '',
    sort_order: category.sort_order
  }
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingId.value = null
}

async function saveCategory() {
  saving.value = true
  try {
    if (editingId.value) {
      await categoriesApi.update(editingId.value, form.value)
    } else {
      await categoriesApi.create(form.value)
    }
    await loadData()
    closeForm()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de l\'enregistrement')
  } finally {
    saving.value = false
  }
}

async function deleteCategory(id) {
  if (!confirm('Supprimer cette catégorie ?')) return

  deleting.value = id
  try {
    await categoriesApi.delete(id)
    await loadData()
  } catch (e) {
    alert(e.response?.data?.message || 'Impossible de supprimer (dépenses existantes)')
  } finally {
    deleting.value = null
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

const colorPresets = [
  '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6',
  '#EC4899', '#06B6D4', '#F97316', '#6366F1', '#84CC16'
]
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Catégories</h1>
        <p class="text-gray-400 mt-1">Catégories de dépenses</p>
      </div>
      <button @click="openCreate" class="btn btn-primary">
        + Nouvelle catégorie
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Categories grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="cat in opsStore.categories"
        :key="cat.id"
        class="card flex items-center gap-4 hover:border-gray-600 transition-colors"
      >
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          :style="{ backgroundColor: cat.color + '20' }"
        >
          <span class="w-6 h-6 rounded-full" :style="{ backgroundColor: cat.color }"></span>
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-medium text-white truncate">{{ cat.name }}</h3>
          <p class="text-sm text-gray-400">
            {{ cat.expense_count || 0 }} dépenses · {{ formatCurrency(cat.total_amount || 0) }}
          </p>
        </div>
        <div class="flex items-center gap-1">
          <button
            @click="openEdit(cat)"
            class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded"
            title="Modifier"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button
            @click="deleteCategory(cat.id)"
            :disabled="deleting === cat.id"
            class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded"
            title="Supprimer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-gray-900/80" @click="closeForm"></div>
      <div class="relative bg-gray-800 rounded-xl p-6 w-full max-w-md border border-gray-700">
        <h2 class="text-xl font-semibold text-white mb-4">
          {{ editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
        </h2>

        <form @submit.prevent="saveCategory" class="space-y-4">
          <div>
            <label for="name">Nom *</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full"
              placeholder="Ex: Frais généraux"
            />
          </div>

          <div>
            <label>Couleur</label>
            <div class="flex items-center gap-2 flex-wrap">
              <button
                v-for="color in colorPresets"
                :key="color"
                type="button"
                @click="form.color = color"
                :class="[
                  'w-8 h-8 rounded-lg transition-transform',
                  form.color === color ? 'ring-2 ring-white ring-offset-2 ring-offset-gray-800 scale-110' : ''
                ]"
                :style="{ backgroundColor: color }"
              />
              <input
                v-model="form.color"
                type="color"
                class="w-8 h-8 rounded cursor-pointer"
              />
            </div>
          </div>

          <div>
            <label for="sort_order">Ordre d'affichage</label>
            <input
              id="sort_order"
              v-model.number="form.sort_order"
              type="number"
              min="0"
              class="w-full"
            />
          </div>

          <div class="flex items-center gap-3 pt-4 border-t border-gray-700">
            <button type="submit" :disabled="saving" class="btn btn-primary">
              {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
            <button type="button" @click="closeForm" class="btn btn-secondary">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
