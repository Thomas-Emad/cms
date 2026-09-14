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
    <section class="mx-auto max-w-7xl px-6 lg:px-10 py-12 lg:py-16">
        <h2
            v-if="props.title"
            class="reveal text-2xl lg:text-3xl"
            style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
            v-reveal
        >
            {{ props.title }}
        </h2>
        <p v-if="props.description" class="reveal mt-2 text-slate-500 max-w-xl" v-reveal="{ delay: 60 }">
            {{ props.description }}
        </p>

        <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
            <div v-for="(experience, i) in data?.experiences ?? []" :key="experience.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <ExperienceCard :experience="experience" />
            </div>
        </div>

        <p v-if="mode === 'edit' && !data?.experiences?.length" class="text-xs text-slate-400 mt-2">
            No experiences match these filters yet.
        </p>
    </section>
</template>
