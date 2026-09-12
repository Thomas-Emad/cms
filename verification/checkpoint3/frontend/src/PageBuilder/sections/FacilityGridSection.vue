<script setup lang="ts">
import FacilityCard from '@/Components/Cards/FacilityCard.vue';
import type { FacilityGridProps, FacilityGridData, SectionSettings, RenderMode } from '@/types/pageBuilder';

defineProps<{
  props: FacilityGridProps;
  settings: SectionSettings;
  data?: FacilityGridData;
  mode: RenderMode;
}>();
</script>

<template>
  <section
    class="mx-auto max-w-screen-sm px-4"
    :class="{
      'py-12': settings.padding === 'large',
      'py-6': settings.padding === 'medium' || !settings.padding,
      'py-3': settings.padding === 'small',
    }"
    :style="{ background: settings.background === 'light' ? '#f8fafc' : undefined }"
  >
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-1">{{ props.title }}</h2>
    <p v-if="props.description" class="text-sm text-slate-500 mb-4">{{ props.description }}</p>

    <div
      class="grid gap-3"
      :class="{
        'grid-cols-2': (props.columns ?? 3) === 2,
        'grid-cols-3': (props.columns ?? 3) === 3,
        'grid-cols-4': (props.columns ?? 3) === 4,
      }"
    >
      <FacilityCard v-for="facility in data?.facilities ?? []" :key="facility.id" :facility="facility" />
    </div>

    <p v-if="mode === 'edit' && !data?.facilities?.length" class="text-xs text-slate-400 mt-2">
      No facilities match these filters yet.
    </p>
  </section>
</template>
