<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Restaurant } from '@/types/restaurant';
import { useI18n } from '@/i18n';

defineProps<{
    restaurant: Pick<Restaurant, 'name' | 'slug' | 'cuisine' | 'location' | 'cover_image_url'>;
}>();

const { t } = useI18n();
</script>

<template>
    <Link :href="`/restaurants/${restaurant.slug}`" class="group relative block aspect-[4/5] overflow-hidden">
        <img
            v-if="restaurant.cover_image_url"
            :src="restaurant.cover_image_url"
            :alt="restaurant.name"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[var(--ease-cinematic)] group-hover:scale-105"
        />
        <div v-else class="absolute inset-0 bg-slate-100" />

        <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />

        <div class="absolute inset-x-0 bottom-0 p-5">
            <p v-if="restaurant.cuisine" class="text-xs uppercase tracking-wide text-white/70">{{ restaurant.cuisine }}</p>
            <h3 class="mt-1 text-xl text-white" style="font-family: var(--font-display)">{{ restaurant.name }}</h3>
            <p v-if="restaurant.location" class="mt-1 text-sm text-white/75">{{ restaurant.location }}</p>
            <span
                class="mt-3 inline-flex items-center gap-1.5 text-xs uppercase tracking-wide text-white/90 opacity-0 -translate-y-1 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0"
            >
                <span>{{ t('common.explore', undefined, 'Explore') }}</span>
                <span class="inline-block transition-transform rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1">→</span>
            </span>
        </div>
    </Link>
</template>
