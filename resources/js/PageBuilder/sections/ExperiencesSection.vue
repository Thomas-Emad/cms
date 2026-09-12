<script setup lang="ts">
import ExperienceCard from '@/Components/Cards/ExperienceCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import type { Experience } from '@/types/content';

interface ExperiencesProps {
  title?: string;
  description?: string | null;
  category?: string | null;
  featured_only?: boolean;
  limit?: number;
}
interface ExperiencesData {
  experiences: Experience[];
}

defineProps<{
  props: ExperiencesProps;
  settings: SectionSettings;
  data?: ExperiencesData;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-1">{{ props.title }}</h2>
    <p v-if="props.description" class="text-sm text-slate-500 mb-4">{{ props.description }}</p>

    <div class="grid grid-cols-2 gap-3">
      <ExperienceCard v-for="experience in data?.experiences ?? []" :key="experience.id" :experience="experience" />
    </div>

    <p v-if="mode === 'edit' && !data?.experiences?.length" class="text-xs text-slate-400 mt-2">
      No experiences match these filters yet.
    </p>
  </section>
</template>
