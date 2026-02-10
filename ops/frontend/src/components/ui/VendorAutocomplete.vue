<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { vendorMappingsApi } from '@/services/api'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Ex: Amazon, IKEA...'
  }
})

const emit = defineEmits(['update:modelValue', 'select', 'blur'])

const inputValue = ref(props.modelValue)
const suggestions = ref([])
const showDropdown = ref(false)
const loading = ref(false)
const highlightedIndex = ref(-1)
const inputRef = ref(null)
const dropdownRef = ref(null)

let debounceTimer = null

watch(() => props.modelValue, (val) => {
  inputValue.value = val
})

async function searchVendors(query) {
  if (!query || query.length < 1) {
    suggestions.value = []
    return
  }

  loading.value = true
  try {
    const response = await vendorMappingsApi.search(query)
    suggestions.value = response.data.data || []
  } catch (e) {
    suggestions.value = []
  } finally {
    loading.value = false
  }
}

function onInput(event) {
  const value = event.target.value
  inputValue.value = value
  emit('update:modelValue', value)
  highlightedIndex.value = -1

  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    searchVendors(value)
    showDropdown.value = true
  }, 200)
}

function selectSuggestion(suggestion) {
  const vendorName = suggestion.vendor_name || suggestion
  inputValue.value = vendorName
  emit('update:modelValue', vendorName)
  emit('select', suggestion)
  showDropdown.value = false
  suggestions.value = []
}

function onFocus() {
  if (inputValue.value && suggestions.value.length > 0) {
    showDropdown.value = true
  }
}

function onBlur() {
  // Delay to allow click on dropdown
  setTimeout(() => {
    showDropdown.value = false
    emit('blur')
  }, 150)
}

function onKeydown(event) {
  if (!showDropdown.value || suggestions.value.length === 0) {
    return
  }

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      highlightedIndex.value = Math.min(highlightedIndex.value + 1, suggestions.value.length - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
      break
    case 'Enter':
      event.preventDefault()
      if (highlightedIndex.value >= 0) {
        selectSuggestion(suggestions.value[highlightedIndex.value])
      }
      break
    case 'Escape':
      showDropdown.value = false
      break
  }
}

function handleClickOutside(event) {
  if (
    inputRef.value &&
    !inputRef.value.contains(event.target) &&
    dropdownRef.value &&
    !dropdownRef.value.contains(event.target)
  ) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  clearTimeout(debounceTimer)
})
</script>

<template>
  <div class="relative">
    <input
      ref="inputRef"
      type="text"
      :value="inputValue"
      :placeholder="placeholder"
      class="w-full"
      autocomplete="off"
      @input="onInput"
      @focus="onFocus"
      @blur="onBlur"
      @keydown="onKeydown"
    />

    <!-- Dropdown -->
    <div
      v-if="showDropdown && suggestions.length > 0"
      ref="dropdownRef"
      class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
    >
      <button
        v-for="(suggestion, index) in suggestions"
        :key="suggestion.vendor_pattern || index"
        type="button"
        class="w-full px-4 py-2 text-left text-sm text-gray-200 hover:bg-gray-700 focus:bg-gray-700 focus:outline-none"
        :class="{ 'bg-gray-700': index === highlightedIndex }"
        @click="selectSuggestion(suggestion)"
      >
        {{ suggestion.vendor_name || suggestion.vendor_display_name || suggestion.vendor_pattern }}
      </button>
    </div>

    <!-- Loading indicator -->
    <div v-if="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
      <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
  </div>
</template>
