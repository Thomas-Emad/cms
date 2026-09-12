<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Experience } from '@/types/content';

defineProps<{
  experience: Pick<Experience, 'title' | 'slug' | 'category' | 'duration' | 'price' | 'cover_image_url'>;
}>();
</script>

<template>
  <Link
    :href="`/experiences/${experience.slug}`"
    class="block overflow-hidden bg-white shadow-sm"
    style="border-radius: var(--radius, 8px)"
  >
    <div class="aspect-[4/3] bg-slate-100">
      <img v-if="experience.cover_image_url" :src="experience.cover_image_url" :alt="experience.title" class="h-full w-full object-cover" />
    </div>
    <div class="p-3">
      <p v-if="experience.category" class="text-xs uppercase tracking-wide text-slate-400">{{ experience.category }}</p>
      <h3 class="font-medium text-slate-800">{{ experience.title }}</h3>
      <p class="mt-0.5 text-sm text-slate-500">
        <span v-if="experience.duration">{{ experience.duration }}</span>
        <span v-if="experience.duration && experience.price"> · </span>
        <span v-if="experience.price">${{ experience.price }}</span>
      </p>
    </div>
  </Link>
</template>
