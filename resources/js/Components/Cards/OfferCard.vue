<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Offer } from '@/types/content';

defineProps<{
    offer: Pick<Offer, 'title' | 'slug' | 'price' | 'discount' | 'cover_image_url'>;
}>();
</script>

<template>
    <Link :href="`/offers/${offer.slug}`" class="group relative block aspect-[4/5] overflow-hidden">
        <img
            v-if="offer.cover_image_url"
            :src="offer.cover_image_url"
            :alt="offer.title"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[var(--ease-cinematic)] group-hover:scale-105"
        />
        <div v-else class="absolute inset-0 bg-slate-100" />

        <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />

        <span
            v-if="offer.discount"
            class="absolute top-4 end-4 text-xs font-medium tracking-wide text-white/90 border border-white/40 rounded-full px-3 py-1 backdrop-blur-sm"
        >
            -{{ offer.discount }}%
        </span>

        <div class="absolute inset-x-0 bottom-0 p-5">
            <h3 class="text-xl text-white" style="font-family: var(--font-display)">{{ offer.title }}</h3>
            <p v-if="offer.price" class="mt-1 text-sm text-white/75">{{ $t('offers.from_price', { price: offer.price }) }}</p>
            <span
                class="mt-3 inline-flex items-center gap-1.5 text-xs uppercase tracking-wide text-white/90 opacity-0 -translate-y-1 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0"
            >
                {{ $t('offers.view_offer') }} <span class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180 inline-block">→</span>
            </span>
        </div>
    </Link>
</template>
