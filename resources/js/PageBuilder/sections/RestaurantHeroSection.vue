<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface Data {
  restaurant: { name: string; subtitle?: string; cover_image_url?: string | null; button_text?: string; reservation_url?: string | null } | null;
}

defineProps<{
  props: { subtitle_override?: string; button_text?: string };
  settings: SectionSettings;
  data?: Data;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="relative flex flex-col items-center justify-center text-center px-4 py-16" style="background: #f8fafc">
    <template v-if="data?.restaurant">
      <h1 class="text-2xl font-semibold" style="color: var(--color-primary, #1F4B5A)">{{ data.restaurant.name }}</h1>
      <p v-if="data.restaurant.subtitle" class="mt-2 text-sm text-slate-500">{{ data.restaurant.subtitle }}</p>
      <a
        v-if="data.restaurant.reservation_url"
        :href="data.restaurant.reservation_url"
        class="mt-4 inline-block px-4 py-2 text-sm font-medium text-white"
        style="background: var(--color-primary, #1F4B5A); border-radius: var(--radius, 8px)"
      >
        {{ data.restaurant.button_text ?? 'Reserve a Table' }}
      </a>
    </template>
    <p v-else-if="mode === 'edit'" class="text-xs text-slate-400">No restaurant context available.</p>
  </section>
</template>
