<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface Data { restaurant: { location?: string | null; floor?: string | null; phone?: string | null } | null; }

defineProps<{
  props: Record<string, never>;
  settings: SectionSettings;
  data?: Data;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6 text-sm text-slate-600">
    <template v-if="data?.restaurant">
      <p v-if="data.restaurant.location">📍 {{ data.restaurant.location }}</p>
      <p v-if="data.restaurant.floor">Floor: {{ data.restaurant.floor }}</p>
      <p v-if="data.restaurant.phone">☎ {{ data.restaurant.phone }}</p>
    </template>
  </section>
</template>
