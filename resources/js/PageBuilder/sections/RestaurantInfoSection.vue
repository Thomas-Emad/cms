<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface Data {
  restaurant: {
    name: string; description?: string | null; cuisine?: string | null;
    dress_code?: string | null; phone?: string | null; opening_hours?: Record<string, string> | null;
  } | null;
}

defineProps<{
  props: { show_opening_hours?: boolean };
  settings: SectionSettings;
  data?: Data;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <template v-if="data?.restaurant">
      <p v-if="data.restaurant.cuisine" class="text-xs uppercase tracking-wide text-slate-400">{{ data.restaurant.cuisine }}</p>
      <p v-if="data.restaurant.description" class="mt-2 text-sm text-slate-600">{{ data.restaurant.description }}</p>
      <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div v-if="data.restaurant.dress_code">
          <p class="text-slate-400">Dress code</p>
          <p class="text-slate-700">{{ data.restaurant.dress_code }}</p>
        </div>
        <div v-if="data.restaurant.phone">
          <p class="text-slate-400">Phone</p>
          <p class="text-slate-700">{{ data.restaurant.phone }}</p>
        </div>
      </div>
      <div v-if="data.restaurant.opening_hours" class="mt-4 text-sm text-slate-600">
        <p class="text-slate-400 mb-1">Opening hours</p>
        <p v-for="(hours, label) in data.restaurant.opening_hours" :key="label">{{ label }}: {{ hours }}</p>
      </div>
    </template>
  </section>
</template>
