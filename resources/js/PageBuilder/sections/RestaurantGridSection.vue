<script setup lang="ts">
import RestaurantCard from '@/Components/Cards/RestaurantCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface RestaurantGridProps {
  title?: string;
  description?: string | null;
  cuisine?: string | null;
  featured_only?: boolean;
  limit?: number;
}
interface RestaurantGridData {
  restaurants: { id: number; name: string; slug: string; cuisine?: string | null; location?: string | null; cover_image_url?: string | null }[];
}

defineProps<{
  props: RestaurantGridProps;
  settings: SectionSettings;
  data?: RestaurantGridData;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-1">{{ props.title }}</h2>
    <p v-if="props.description" class="text-sm text-slate-500 mb-4">{{ props.description }}</p>

    <div class="grid grid-cols-1 gap-3">
      <RestaurantCard v-for="restaurant in data?.restaurants ?? []" :key="restaurant.id" :restaurant="restaurant" />
    </div>

    <p v-if="mode === 'edit' && !data?.restaurants?.length" class="text-xs text-slate-400 mt-2">
      No restaurants match these filters yet.
    </p>
  </section>
</template>
