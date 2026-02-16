<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

// Liste des pays avec indicatifs, placeholders et formats
// format: tableau de tailles de groupes pour le formatage
const countries = [
  { code: 'FR', name: 'France', dial: '+33', flag: '\u{1F1EB}\u{1F1F7}', placeholder: '06 12 34 56 78', format: [2, 2, 2, 2, 2] },
  { code: 'BE', name: 'Belgique', dial: '+32', flag: '\u{1F1E7}\u{1F1EA}', placeholder: '0470 12 34 56', format: [4, 2, 2, 2] },
  { code: 'GB', name: 'Royaume-Uni', dial: '+44', flag: '\u{1F1EC}\u{1F1E7}', placeholder: '07911 123 456', format: [5, 3, 3] },
  { code: 'CH', name: 'Suisse', dial: '+41', flag: '\u{1F1E8}\u{1F1ED}', placeholder: '076 123 45 67', format: [3, 3, 2, 2] },
  { code: 'LU', name: 'Luxembourg', dial: '+352', flag: '\u{1F1F1}\u{1F1FA}', placeholder: '621 123 456', format: [3, 3, 3] },
  { code: 'DE', name: 'Allemagne', dial: '+49', flag: '\u{1F1E9}\u{1F1EA}', placeholder: '0151 1234 5678', format: [4, 4, 4] },
  { code: 'ES', name: 'Espagne', dial: '+34', flag: '\u{1F1EA}\u{1F1F8}', placeholder: '612 34 56 78', format: [3, 2, 2, 2] },
  { code: 'IT', name: 'Italie', dial: '+39', flag: '\u{1F1EE}\u{1F1F9}', placeholder: '312 345 6789', format: [3, 3, 4] },
  { code: 'MC', name: 'Monaco', dial: '+377', flag: '\u{1F1F2}\u{1F1E8}', placeholder: '06 12 34 56 78', format: [2, 2, 2, 2, 2] },
  { code: 'NL', name: 'Pays-Bas', dial: '+31', flag: '\u{1F1F3}\u{1F1F1}', placeholder: '06 1234 5678', format: [2, 4, 4] },
  { code: 'PT', name: 'Portugal', dial: '+351', flag: '\u{1F1F5}\u{1F1F9}', placeholder: '912 345 678', format: [3, 3, 3] }
]

const selectedCountry = ref(countries[0]) // France par défaut
const phoneNumber = ref('')
const dropdownOpen = ref(false)

// Placeholder dynamique selon le pays
const dynamicPlaceholder = computed(() => {
  return selectedCountry.value.placeholder
})

// Reformater un numéro avec le format du pays actuel
function reformatNumber(digits) {
  if (!digits) {
    phoneNumber.value = ''
    return
  }

  const format = selectedCountry.value.format
  const groups = []
  let position = 0

  for (const size of format) {
    if (position >= digits.length) break
    groups.push(digits.substring(position, position + size))
    position += size
  }

  if (position < digits.length) {
    groups.push(digits.substring(position))
  }

  phoneNumber.value = groups.join(' ')
}

// Parser le numéro initial si fourni
function parseInitialValue() {
  const value = props.modelValue
  if (!value) {
    phoneNumber.value = ''
    return
  }

  // Chercher si le numéro commence par un indicatif connu
  for (const country of countries) {
    if (value.startsWith(country.dial)) {
      selectedCountry.value = country
      const digits = value.substring(country.dial.length)
      reformatNumber(digits)
      return
    }
  }

  // Si pas d'indicatif reconnu mais commence par +, garder tel quel
  if (value.startsWith('+')) {
    phoneNumber.value = value
  } else {
    // Sinon, considérer comme numéro français
    const digits = value.startsWith('0') ? value.substring(1) : value
    reformatNumber(digits)
  }
}

// Construire la valeur complète avec indicatif
const fullValue = computed(() => {
  const cleaned = phoneNumber.value.replace(/[\s\-\.\(\)]/g, '')
  if (!cleaned) return ''

  // Si le numéro saisi commence par +, le garder tel quel
  if (cleaned.startsWith('+')) {
    return cleaned
  }

  // Si commence par 0, retirer le 0 et ajouter l'indicatif
  if (cleaned.startsWith('0')) {
    return selectedCountry.value.dial + cleaned.substring(1)
  }

  return selectedCountry.value.dial + cleaned
})

// Emettre la valeur quand elle change
watch(fullValue, (newValue) => {
  emit('update:modelValue', newValue)
})

// Initialiser au montage
parseInitialValue()

// Re-parser si la valeur externe change
watch(() => props.modelValue, (newValue, oldValue) => {
  // Éviter la boucle infinie
  if (newValue !== fullValue.value) {
    parseInitialValue()
  }
})

function selectCountry(country) {
  selectedCountry.value = country
  dropdownOpen.value = false
  // Reformater le numéro avec le nouveau format
  if (phoneNumber.value) {
    const digits = phoneNumber.value.replace(/\s/g, '')
    reformatNumber(digits)
  }
}

function toggleDropdown() {
  if (!props.disabled) {
    dropdownOpen.value = !dropdownOpen.value
  }
}

function closeDropdown() {
  dropdownOpen.value = false
}

// Formater l'affichage du numéro selon le format du pays
function formatInput(e) {
  let value = e.target.value.replace(/[^\d\s]/g, '')
  // Limiter à 15 chiffres
  const digits = value.replace(/\s/g, '').substring(0, 15)
  reformatNumber(digits)
}
</script>

<template>
  <div class="phone-input-wrapper">
    <div class="flex">
      <!-- Sélecteur de pays -->
      <div class="relative">
        <button
          type="button"
          @click="toggleDropdown"
          @blur="closeDropdown"
          :disabled="disabled"
          class="flex items-center gap-1 px-3 py-2 text-sm bg-gray-700 border border-gray-600 border-r-0 rounded-l-lg text-white hover:bg-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span class="text-lg">{{ selectedCountry.flag }}</span>
          <span class="text-gray-400">{{ selectedCountry.dial }}</span>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Dropdown -->
        <div
          v-if="dropdownOpen"
          class="absolute z-50 mt-1 w-56 bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
          <button
            v-for="country in countries"
            :key="country.code"
            type="button"
            @mousedown.prevent="selectCountry(country)"
            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-gray-700 text-white"
            :class="{ 'bg-gray-700': country.code === selectedCountry.code }"
          >
            <span class="text-lg">{{ country.flag }}</span>
            <span class="flex-1">{{ country.name }}</span>
            <span class="text-gray-400">{{ country.dial }}</span>
          </button>
        </div>
      </div>

      <!-- Champ numéro -->
      <input
        type="tel"
        :value="phoneNumber"
        @input="formatInput"
        :placeholder="dynamicPlaceholder"
        :required="required"
        :disabled="disabled"
        class="flex-1 px-4 py-2 text-sm bg-gray-700 border border-gray-600 rounded-r-lg text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
      />
    </div>
  </div>
</template>

<style scoped>
.phone-input-wrapper {
  position: relative;
}
</style>
