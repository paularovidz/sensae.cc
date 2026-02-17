<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import ImpersonationBanner from '@/components/ui/ImpersonationBanner.vue'

const route = useRoute()

// Check if we're in embed mode (via query param or route meta)
const isEmbed = computed(() => {
  return route.query.embed === 'true' || route.meta.embed === true
})
</script>

<template>
  <div class="min-h-screen bg-black relative overflow-hidden">
    <!-- Background gradient -->
    <div class="section-gradient" />

    <!-- Impersonation Banner (above header) -->
    <ImpersonationBanner />

    <!-- Header: Logo only, centered -->
    <header v-if="!isEmbed" class="relative z-10 py-8">
      <div class="flex justify-center">
        <span class="text-3xl font-logo text-white">sensëa</span>
      </div>
    </header>

    <!-- Main content -->
    <main :class="['relative z-10', isEmbed ? 'py-4' : 'py-8']">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <router-view />
      </div>
    </main>
  </div>
</template>

<style scoped>
.section-gradient::after {
  pointer-events: none;
  bottom: 18rem;
  --tw-translate-x: -50%;
  width: 80%;
  height: 540px;
  translate: var(--tw-translate-x) var(--tw-translate-y);
  content: "";
  background-image: radial-gradient(50% 50%, #721ad64d, transparent);
  position: absolute;
  left: 50%;
}

.section-gradient {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}
</style>
