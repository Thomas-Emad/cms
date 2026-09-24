<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Facility } from '@/types/facility';
import { useI18n } from '@/i18n';

defineProps<{
    facility: Pick<Facility, 'name' | 'slug' | 'short_description' | 'category' | 'cover_image_url'>;
}>();

const { t } = useI18n();
</script>

<template>
    <Link :href="`/facilities/${facility.slug}`" class="group relative block aspect-[4/5] overflow-hidden">
        <img
            v-if="facility.cover_image_url"
            :src="facility.cover_image_url"
            :alt="facility.name"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[var(--ease-cinematic)] group-hover:scale-105"
        />
        <div v-else class="absolute inset-0 bg-slate-100" />

        <!-- Content lives on a gradient scrim over the image, not in a
         boxed panel below it - keeps the image the dominant element. -->
        <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />

        <div class="absolute inset-x-0 bottom-0 p-5">
            <p v-if="facility.category" class="text-xs uppercase tracking-wide text-white/70">
                {{ t(`facilities.categories.${facility.category}`, undefined, facility.category) }}
            </p>
            <h3 class="mt-1 text-xl text-white" style="font-family: var(--font-display)">{{ facility.name }}</h3>
            <p v-if="facility.short_description" class="mt-1 text-sm text-white/75 line-clamp-2">
                {{ facility.short_description }}
            </p>
            <span
                class="mt-3 inline-flex items-center gap-1.5 text-xs uppercase tracking-wide text-white/90 opacity-0 -translate-y-1 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0"
            >
                <span>{{ t('common.explore', undefined, 'Explore') }}</span>
                <span class="inline-block transition-transform rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1">→</span>
            </span>
        </div>
    </Link>
</template>
