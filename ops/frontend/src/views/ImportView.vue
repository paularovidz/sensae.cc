<script setup>
import { onMounted, ref } from 'vue'
import { useOpsStore } from '@/stores/ops'
import { importApi } from '@/services/api'

const opsStore = useOpsStore()
const loading = ref(false)
const importing = ref(false)
const history = ref([])
const preview = ref(null)
const file = ref(null)
const error = ref(null)

const config = ref({
  date_column: 0,
  description_column: 1,
  amount_column: 2,
  vendor_column: '',
  date_format: 'd/m/Y',
  skip_header: true
})

async function loadHistory() {
  try {
    const response = await importApi.history(20)
    history.value = response.data.data
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  loadHistory()
  opsStore.fetchCategories()
})

function handleFileSelect(event) {
  const selectedFile = event.target.files[0]
  if (selectedFile) {
    file.value = selectedFile
    preview.value = null
    error.value = null
  }
}

async function previewFile() {
  if (!file.value) return

  loading.value = true
  error.value = null

  try {
    const formData = new FormData()
    formData.append('file', file.value)
    formData.append('date_column', config.value.date_column)
    formData.append('description_column', config.value.description_column)
    formData.append('amount_column', config.value.amount_column)
    if (config.value.vendor_column !== '') {
      formData.append('vendor_column', config.value.vendor_column)
    }
    formData.append('date_format', config.value.date_format)
    formData.append('skip_header', config.value.skip_header ? 'true' : 'false')

    const response = await importApi.preview(formData)
    preview.value = response.data.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la prévisualisation'
  } finally {
    loading.value = false
  }
}

async function importFile() {
  if (!file.value || !preview.value) return

  importing.value = true
  error.value = null

  try {
    const formData = new FormData()
    formData.append('file', file.value)
    formData.append('date_column', config.value.date_column)
    formData.append('description_column', config.value.description_column)
    formData.append('amount_column', config.value.amount_column)
    if (config.value.vendor_column !== '') {
      formData.append('vendor_column', config.value.vendor_column)
    }
    formData.append('date_format', config.value.date_format)
    formData.append('skip_header', config.value.skip_header ? 'true' : 'false')

    const response = await importApi.import(formData)
    const result = response.data.data

    alert(`Import terminé: ${result.imported} lignes importées, ${result.skipped} ignorées`)

    // Reset
    file.value = null
    preview.value = null
    await loadHistory()
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'import'
  } finally {
    importing.value = false
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(value)
}

const dateFormats = [
  { value: 'd/m/Y', label: 'DD/MM/YYYY (France)' },
  { value: 'Y-m-d', label: 'YYYY-MM-DD (ISO)' },
  { value: 'd-m-Y', label: 'DD-MM-YYYY' },
  { value: 'm/d/Y', label: 'MM/DD/YYYY (US)' }
]
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">Import CSV</h1>
      <p class="text-gray-400 mt-1">Importer des dépenses depuis un relevé bancaire</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Configuration -->
      <div class="lg:col-span-2 space-y-6">
        <!-- File upload -->
        <div class="card">
          <h3 class="text-lg font-semibold text-white mb-4">1. Sélectionner le fichier</h3>
          <input
            type="file"
            accept=".csv,.txt"
            @change="handleFileSelect"
            class="block w-full text-sm text-gray-400
              file:mr-4 file:py-2 file:px-4
              file:rounded-lg file:border-0
              file:text-sm file:font-medium
              file:bg-ops-600 file:text-white
              hover:file:bg-ops-500
              cursor-pointer"
          />
          <p v-if="file" class="mt-2 text-sm text-gray-400">
            Fichier: {{ file.name }} ({{ (file.size / 1024).toFixed(1) }} Ko)
          </p>
        </div>

        <!-- Column mapping -->
        <div class="card">
          <h3 class="text-lg font-semibold text-white mb-4">2. Configuration des colonnes</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="date_column">Colonne date</label>
              <input
                id="date_column"
                v-model.number="config.date_column"
                type="number"
                min="0"
                class="w-full"
              />
            </div>
            <div>
              <label for="description_column">Colonne description</label>
              <input
                id="description_column"
                v-model.number="config.description_column"
                type="number"
                min="0"
                class="w-full"
              />
            </div>
            <div>
              <label for="amount_column">Colonne montant</label>
              <input
                id="amount_column"
                v-model.number="config.amount_column"
                type="number"
                min="0"
                class="w-full"
              />
            </div>
            <div>
              <label for="vendor_column">Colonne fournisseur (optionnel)</label>
              <input
                id="vendor_column"
                v-model="config.vendor_column"
                type="number"
                min="0"
                class="w-full"
                placeholder="Laisser vide si absent"
              />
            </div>
            <div>
              <label for="date_format">Format de date</label>
              <select id="date_format" v-model="config.date_format" class="w-full">
                <option v-for="fmt in dateFormats" :key="fmt.value" :value="fmt.value">
                  {{ fmt.label }}
                </option>
              </select>
            </div>
            <div class="flex items-center gap-2 pt-6">
              <input
                id="skip_header"
                v-model="config.skip_header"
                type="checkbox"
                class="w-4 h-4 text-ops-600 bg-gray-700 border-gray-600 rounded"
              />
              <label for="skip_header" class="text-sm text-gray-300">
                Ignorer la première ligne (en-têtes)
              </label>
            </div>
          </div>

          <div class="mt-4">
            <button
              @click="previewFile"
              :disabled="!file || loading"
              class="btn btn-secondary"
            >
              {{ loading ? 'Analyse...' : 'Prévisualiser' }}
            </button>
          </div>
        </div>

        <!-- Error -->
        <div v-if="error" class="p-4 bg-red-900/50 border border-red-700 rounded-lg text-red-400">
          {{ error }}
        </div>

        <!-- Preview -->
        <div v-if="preview" class="card">
          <h3 class="text-lg font-semibold text-white mb-4">
            3. Prévisualisation ({{ preview.rows.length }} / {{ preview.total_rows }} lignes)
          </h3>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Fournisseur</th>
                  <th>Catégorie suggérée</th>
                  <th class="text-right">Montant</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in preview.rows" :key="index">
                  <td>{{ row.date }}</td>
                  <td class="max-w-xs truncate">{{ row.description }}</td>
                  <td>{{ row.suggested_vendor || row.vendor || '-' }}</td>
                  <td>
                    <span v-if="row.suggested_category_name" class="text-ops-400">
                      {{ row.suggested_category_name }}
                    </span>
                    <span v-else class="text-gray-500">Frais généraux</span>
                  </td>
                  <td class="text-right" :class="row.amount < 0 ? 'text-red-400' : 'text-green-400'">
                    {{ formatCurrency(row.amount) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-700">
            <p class="text-sm text-gray-400 mb-4">
              Seules les lignes avec un montant négatif (dépenses) seront importées.
            </p>
            <button
              @click="importFile"
              :disabled="importing"
              class="btn btn-primary"
            >
              {{ importing ? 'Import en cours...' : 'Lancer l\'import' }}
            </button>
          </div>
        </div>
      </div>

      <!-- History -->
      <div class="card h-fit">
        <h3 class="text-lg font-semibold text-white mb-4">Historique des imports</h3>

        <div v-if="!history.length" class="text-gray-400 text-sm">
          Aucun import effectué
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="item in history"
            :key="item.id"
            class="p-3 bg-gray-700/50 rounded-lg"
          >
            <p class="text-sm font-medium text-white truncate">{{ item.filename }}</p>
            <p class="text-xs text-gray-400 mt-1">
              {{ formatDate(item.import_date) }}
            </p>
            <p class="text-xs text-gray-400">
              {{ item.rows_imported }} importées, {{ item.rows_skipped }} ignorées
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
