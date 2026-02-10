<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  value: [Number, String],
  subtitle: String,
  change: Number,
  changeLabel: String,
  format: {
    type: String,
    default: 'currency'
  },
  color: {
    type: String,
    default: 'ops'
  },
  loading: Boolean
})

const formattedValue = computed(() => {
  if (props.loading) return '...'
  if (props.value === null || props.value === undefined) return '-'

  if (props.format === 'currency') {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(props.value)
  }

  if (props.format === 'number') {
    return new Intl.NumberFormat('fr-FR').format(props.value)
  }

  return props.value
})

const changeClass = computed(() => {
  if (!props.change) return ''
  return props.change > 0 ? 'positive' : 'negative'
})

const formattedChange = computed(() => {
  if (!props.change) return null
  const sign = props.change > 0 ? '+' : ''
  return `${sign}${props.change.toFixed(1)}%`
})

const colorClasses = computed(() => {
  const colors = {
    ops: 'border-l-ops-500',
    green: 'border-l-green-500',
    red: 'border-l-red-500',
    blue: 'border-l-blue-500',
    yellow: 'border-l-yellow-500',
    purple: 'border-l-purple-500'
  }
  return colors[props.color] || colors.ops
})
</script>

<template>
  <div :class="['kpi-card border-l-4', colorClasses]">
    <p class="text-sm text-gray-400 mb-1">{{ title }}</p>
    <p class="kpi-value">{{ formattedValue }}</p>
    <p v-if="subtitle" class="kpi-label">{{ subtitle }}</p>
    <p v-if="change" :class="['kpi-change', changeClass]">
      <svg v-if="change > 0" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
      <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
      {{ formattedChange }}
      <span v-if="changeLabel" class="text-gray-500 ml-1">{{ changeLabel }}</span>
    </p>
  </div>
</template>
