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
    <section class="mx-auto max-w-7xl px-6 lg:px-10 py-16 lg:py-24">
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

        <div class="mt-8 grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            <div v-for="(restaurant, i) in data?.restaurants ?? []" :key="restaurant.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <RestaurantCard :restaurant="restaurant" />
            </div>
        </div>

        <p v-if="mode === 'edit' && !data?.restaurants?.length" class="text-xs text-slate-400 mt-2">
            No restaurants match these filters yet.
        </p>
    </section>
</template>
