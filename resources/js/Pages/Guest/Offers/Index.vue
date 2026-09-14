<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import OfferCard from '@/Components/Cards/OfferCard.vue';
import type { Offer } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ offers: Offer[] }>();
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
        <h1 class="reveal text-3xl lg:text-4xl mb-8 lg:mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>
            Special Offers
        </h1>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
            <div v-for="(offer, i) in offers" :key="offer.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <OfferCard :offer="offer" />
            </div>
        </div>
    </div>
</template>
