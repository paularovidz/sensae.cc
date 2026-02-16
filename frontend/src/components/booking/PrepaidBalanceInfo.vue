<script setup>
import { computed } from 'vue'

const props = defineProps({
  balance: {
    type: Object,
    required: true
  },
  usePrepaid: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:usePrepaid'])

const hasCredits = computed(() => props.balance?.total_credits > 0)
</script>

<template>
  <div v-if="hasCredits" class="bg-teal-50 border border-teal-200 rounded-lg p-4">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0">
        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="flex-1">
        <h4 class="font-medium text-teal-800">
          Vous avez {{ balance.total_credits }} séance{{ balance.total_credits > 1 ? 's' : '' }} prépayée{{ balance.total_credits > 1 ? 's' : '' }}
        </h4>
        <p class="text-sm text-teal-700 mt-1">
          Vous pouvez utiliser vos crédits pour cette réservation.
        </p>

        <label class="flex items-center gap-2 mt-3 cursor-pointer">
          <input
            type="checkbox"
            :checked="usePrepaid"
            @change="emit('update:usePrepaid', $event.target.checked)"
            class="w-4 h-4 text-teal-600 border-teal-300 rounded focus:ring-teal-500"
          />
          <span class="text-sm font-medium text-teal-800">
            Utiliser une séance prépayée (0 € à payer)
          </span>
        </label>
      </div>
    </div>
  </div>
</template>
