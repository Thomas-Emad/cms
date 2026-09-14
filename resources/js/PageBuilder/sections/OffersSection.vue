<script setup lang="ts">
import OfferCard from '@/Components/Cards/OfferCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import type { Offer } from '@/types/content';

interface OffersProps {
    title?: string;
    limit?: number;
    active_only?: boolean;
    featured_only?: boolean;
}
interface OffersData {
    offers: Offer[];
}

defineProps<{
    props: OffersProps;
    settings: SectionSettings;
    data?: OffersData;
    mode: RenderMode;
}>();
</script>

<template>
    <section class="mx-auto max-w-7xl px-6 lg:px-10 py-16 lg:py-24">
        <h2
            v-if="props.title"
            class="reveal text-4xl lg:text-6xl leading-[1.05] mb-10"
            style="font-family: var(--font-display); color: var(--luxury-forest)"
            v-reveal
        >
            {{ props.title }}
        </h2>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
            <div v-for="(offer, i) in data?.offers ?? []" :key="offer.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <OfferCard :offer="offer" />
            </div>
        </div>

        <p v-if="mode === 'edit' && !data?.offers?.length" class="text-xs text-slate-400 mt-2">
            No offers match these filters yet.
        </p>
    </section>
</template>
