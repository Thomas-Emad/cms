<script setup lang="ts">
import type { Service } from '@/types/content';

defineProps<{
  service: Service;
}>();
</script>

<template>
  <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3">
    <div>
      <h3 class="font-medium text-slate-800">{{ service.name }}</h3>
      <p v-if="service.description" class="text-sm text-slate-500">{{ service.description }}</p>
    </div>
    <button
      v-if="service.request_enabled"
      type="button"
      class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium text-white"
      style="background: var(--color-primary, #1F4B5A)"
    >
      Request
    </button>
    <span v-else-if="service.price" class="shrink-0 text-sm text-slate-600">${{ service.price }}</span>
  </div>
</template>
