<script setup>
import { onMounted, ref } from 'vue'
import { useOpsStore } from '@/stores/ops'
import { vendorMappingsApi } from '@/services/api'

const opsStore = useOpsStore()
const loading = ref(true)
const saving = ref(false)
const deleting = ref(null)
const showForm = ref(false)
const editingId = ref(null)
const mappings = ref([])

const form = ref({
  vendor_pattern: '',
  vendor_display_name: '',
  category_id: '',
  is_regex: false,
  priority: 0,
  notes: ''
})

async function loadData() {
  loading.value = true
  try {
    const [mappingsRes] = await Promise.all([
      vendorMappingsApi.getAll(),
      opsStore.fetchCategories()
    ])
    mappings.value = mappingsRes.data.data
  } finally {
    loading.value = false
  }
}

onMounted(loadData)

function openCreate() {
  editingId.value = null
  form.value = {
    vendor_pattern: '',
    vendor_display_name: '',
    category_id: '',
    is_regex: false,
    priority: 0,
    notes: ''
  }
  showForm.value = true
}

function openEdit(mapping) {
  editingId.value = mapping.id
  form.value = {
    vendor_pattern: mapping.vendor_pattern,
    vendor_display_name: mapping.vendor_display_name || '',
    category_id: mapping.category_id,
    is_regex: !!mapping.is_regex,
    priority: mapping.priority,
    notes: mapping.notes || ''
  }
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingId.value = null
}

async function saveMapping() {
  saving.value = true
  try {
    const data = {
      ...form.value,
      is_regex: form.value.is_regex ? 1 : 0
    }

    if (editingId.value) {
      await vendorMappingsApi.update(editingId.value, data)
    } else {
      await vendorMappingsApi.create(data)
    }
    await loadData()
    closeForm()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de l\'enregistrement')
  } finally {
    saving.value = false
  }
}

async function deleteMapping(id) {
  if (!confirm('Supprimer ce mapping ?')) return

  deleting.value = id
  try {
    await vendorMappingsApi.delete(id)
    await loadData()
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur')
  } finally {
    deleting.value = null
  }
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-white">Mappings fournisseurs</h1>
        <p class="text-gray-400 mt-1">Auto-catégorisation des dépenses par fournisseur</p>
      </div>
      <button @click="openCreate" class="btn btn-primary">
        + Nouveau mapping
      </button>
    </div>

    <!-- Info -->
    <div class="mb-6 p-4 bg-blue-900/20 border border-blue-800 rounded-lg">
      <p class="text-sm text-blue-300">
        Les mappings permettent d'assigner automatiquement une catégorie aux dépenses
        lors de l'import CSV ou de la saisie manuelle.
      </p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card text-center py-12 text-gray-400">
      Chargement...
    </div>

    <!-- Table -->
    <div v-else class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table>
          <thead>
            <tr>
              <th>Pattern</th>
              <th>Nom affiché</th>
              <th>Catégorie</th>
              <th>Type</th>
              <th>Priorité</th>
              <th class="w-20"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!mappings.length">
              <td colspan="6" class="text-center py-8 text-gray-400">
                Aucun mapping configuré
              </td>
            </tr>
            <tr v-for="mapping in mappings" :key="mapping.id">
              <td class="font-mono text-sm">{{ mapping.vendor_pattern }}</td>
              <td>{{ mapping.vendor_display_name || '-' }}</td>
              <td>
                <span
                  class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium"
                  :style="{ backgroundColor: mapping.category_color + '20', color: mapping.category_color }"
                >
                  <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: mapping.category_color }"></span>
                  {{ mapping.category_name }}
                </span>
              </td>
              <td>
                <span :class="['badge', mapping.is_regex ? 'badge-info' : 'badge-success']">
                  {{ mapping.is_regex ? 'Regex' : 'Texte' }}
                </span>
              </td>
              <td>{{ mapping.priority }}</td>
              <td>
                <div class="flex items-center justify-end gap-1">
                  <button
                    @click="openEdit(mapping)"
                    class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded"
                    title="Modifier"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="deleteMapping(mapping.id)"
                    :disabled="deleting === mapping.id"
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

    <!-- Modal Form -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-gray-900/80" @click="closeForm"></div>
      <div class="relative bg-gray-800 rounded-xl p-6 w-full max-w-lg border border-gray-700">
        <h2 class="text-xl font-semibold text-white mb-4">
          {{ editingId ? 'Modifier le mapping' : 'Nouveau mapping' }}
        </h2>

        <form @submit.prevent="saveMapping" class="space-y-4">
          <div>
            <label for="vendor_pattern">Pattern fournisseur *</label>
            <input
              id="vendor_pattern"
              v-model="form.vendor_pattern"
              type="text"
              required
              class="w-full font-mono"
              placeholder="Ex: AMAZON ou .*PAYPAL.*"
            />
            <p class="text-xs text-gray-500 mt-1">
              Texte à rechercher dans le libellé (ou regex si coché)
            </p>
          </div>

          <div>
            <label for="vendor_display_name">Nom d'affichage</label>
            <input
              id="vendor_display_name"
              v-model="form.vendor_display_name"
              type="text"
              class="w-full"
              placeholder="Ex: Amazon"
            />
            <p class="text-xs text-gray-500 mt-1">
              Nom propre affiché à la place du libellé brut
            </p>
          </div>

          <div>
            <label for="category_id">Catégorie *</label>
            <select id="category_id" v-model="form.category_id" required class="w-full">
              <option value="">Sélectionner une catégorie</option>
              <option v-for="cat in opsStore.categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <input
                id="is_regex"
                v-model="form.is_regex"
                type="checkbox"
                class="w-4 h-4 text-ops-600 bg-gray-700 border-gray-600 rounded"
              />
              <label for="is_regex" class="text-sm text-gray-300">Expression régulière</label>
            </div>

            <div class="flex-1">
              <label for="priority" class="text-sm">Priorité</label>
              <input
                id="priority"
                v-model.number="form.priority"
                type="number"
                min="0"
                class="w-20 ml-2"
              />
            </div>
          </div>

          <div>
            <label for="notes">Notes</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="2"
              class="w-full"
              placeholder="Notes optionnelles..."
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
