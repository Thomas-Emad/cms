<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import OfferCard from '@/Components/Cards/OfferCard.vue';
import type { Offer } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ offers: Offer[] }>();
</script>

<template>
  <div class="mx-auto max-w-screen-sm px-4 py-6">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Special Offers</h1>
    <div class="grid grid-cols-2 gap-3">
      <OfferCard v-for="offer in offers" :key="offer.id" :offer="offer" />
    </div>
  </div>
</template>
