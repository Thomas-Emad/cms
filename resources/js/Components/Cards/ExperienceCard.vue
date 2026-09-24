<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Experience } from '@/types/content';

defineProps<{
    experience: Pick<Experience, 'title' | 'slug' | 'category' | 'duration' | 'price' | 'cover_image_url'>;
}>();
</script>

<template>
    <Link :href="`/experiences/${experience.slug}`" class="group relative block aspect-[4/5] overflow-hidden">
        <img
            v-if="experience.cover_image_url"
            :src="experience.cover_image_url"
            :alt="experience.title"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[var(--ease-cinematic)] group-hover:scale-105"
        />
        <div v-else class="absolute inset-0 bg-slate-100" />

        <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />

        <div class="absolute inset-x-0 bottom-0 p-5">
            <p v-if="experience.category" class="text-xs uppercase tracking-wide text-white/70">{{ experience.category }}</p>
            <h3 class="mt-1 text-xl text-white" style="font-family: var(--font-display)">{{ experience.title }}</h3>
            <p class="mt-1 text-sm text-white/75">
                <span v-if="experience.duration">{{ experience.duration }}</span>
                <span v-if="experience.duration && experience.price"> · </span>
                <span v-if="experience.price">${{ experience.price }}</span>
            </p>
            <span
                class="mt-3 inline-flex items-center gap-1.5 text-xs uppercase tracking-wide text-white/90 opacity-0 -translate-y-1 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0"
            >
                {{ $t('common.explore') }} <span class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180 inline-block">→</span>
            </span>
        </div>
    </Link>
</template>
