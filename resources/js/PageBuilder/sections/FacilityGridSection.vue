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
        class="mx-auto max-w-7xl px-6 lg:px-10"
        :class="{
            'py-20 lg:py-28': settings.padding === 'large',
            'py-12 lg:py-16': settings.padding === 'medium' || !settings.padding,
            'py-6 lg:py-8': settings.padding === 'small',
        }"
        :style="{ background: settings.background === 'light' ? 'var(--luxury-cream)' : undefined }"
    >
        <h2
            v-if="props.title"
            class="reveal text-4xl lg:text-6xl leading-[1.05]"
            style="font-family: var(--font-display); color: var(--luxury-forest)"
            v-reveal
        >
            {{ props.title }}
        </h2>
        <p v-if="props.description" class="reveal mt-4 text-stone-600 max-w-xl leading-relaxed" v-reveal="{ delay: 60 }">
            {{ props.description }}
        </p>

        <div
            class="mt-8 grid gap-4 lg:gap-5 grid-cols-2"
            :class="{
                'lg:grid-cols-2': (props.columns ?? 3) === 2,
                'lg:grid-cols-3': (props.columns ?? 3) === 3,
                'lg:grid-cols-4': (props.columns ?? 3) === 4,
            }"
        >
            <div v-for="(facility, i) in data?.facilities ?? []" :key="facility.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <FacilityCard :facility="facility" />
            </div>
        </div>

        <p v-if="mode === 'edit' && !data?.facilities?.length" class="text-xs text-slate-400 mt-2">
            No facilities match these filters yet.
        </p>
    </section>
</template>
