<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { Experience } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ experience: Experience }>();
</script>

<template>
  <div>
    <div class="aspect-[16/9] bg-slate-100">
      <img v-if="experience.cover_image_url" :src="experience.cover_image_url" :alt="experience.title" class="h-full w-full object-cover" />
    </div>
    <div class="mx-auto max-w-screen-sm px-4 py-6">
      <p v-if="experience.category" class="text-xs uppercase tracking-wide text-slate-400">{{ experience.category }}</p>
      <h1 class="text-xl font-semibold text-slate-800">{{ experience.title }}</h1>
      <p class="mt-1 text-sm text-slate-500">
        <span v-if="experience.duration">{{ experience.duration }}</span>
        <span v-if="experience.duration && experience.price"> · </span>
        <span v-if="experience.price">${{ experience.price }}</span>
      </p>
      <p v-if="experience.description" class="mt-4 text-sm text-slate-600 whitespace-pre-line">{{ experience.description }}</p>
      <a
        v-if="experience.booking_url"
        :href="experience.booking_url"
        class="mt-6 inline-block rounded-md px-4 py-2 text-sm font-medium text-white"
        style="background: var(--color-primary, #1F4B5A)"
      >
        Book this experience
      </a>
    </div>
  </div>
</template>
