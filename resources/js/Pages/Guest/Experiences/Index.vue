<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ExperienceCard from '@/Components/Cards/ExperienceCard.vue';
import type { Experience } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ experiences: Experience[]; category: string | null }>();
</script>

<template>
  <div class="mx-auto max-w-screen-sm px-4 py-6">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Experiences</h1>
    <div class="grid grid-cols-2 gap-3">
      <ExperienceCard v-for="experience in experiences" :key="experience.id" :experience="experience" />
    </div>
  </div>
</template>
