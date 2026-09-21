<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';
import RestaurantCard from '@/Components/Cards/RestaurantCard.vue';
import type { Restaurant } from '@/types/restaurant';

defineOptions({ layout: GuestShell });

defineProps<{
    restaurants: Restaurant[];
}>();
</script>

<template>
    <!-- pt-24 lg:pt-28 clears the fixed nav since this page has no hero
       to sit behind (see GuestLayout's <main> comment). -->
    <div class="mx-auto max-w-7xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
        <h1 class="reveal text-3xl lg:text-4xl mb-8 lg:mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>
            Dining
        </h1>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            <div v-for="(r, i) in restaurants" :key="r.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <RestaurantCard :restaurant="r" />
            </div>
        </div>
    </div>
</template>
