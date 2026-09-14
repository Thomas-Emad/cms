<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ExperienceCard from '@/Components/Cards/ExperienceCard.vue';
import type { Experience } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ experiences: Experience[]; category: string | null }>();
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
        <h1 class="reveal text-3xl lg:text-4xl mb-8 lg:mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>
            Experiences
        </h1>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
            <div v-for="(experience, i) in experiences" :key="experience.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <ExperienceCard :experience="experience" />
            </div>
        </div>
    </div>
</template>
