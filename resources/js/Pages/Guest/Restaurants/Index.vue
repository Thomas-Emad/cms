<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import RestaurantCard from '@/Components/Cards/RestaurantCard.vue';
import type { Restaurant } from '@/types/restaurant';

defineOptions({ layout: GuestLayout });

defineProps<{
  restaurants: Restaurant[];
}>();
</script>

<template>
  <div class="mx-auto max-w-screen-sm px-4 py-6">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Dining</h1>
    <div class="grid grid-cols-1 gap-3">
      <RestaurantCard v-for="r in restaurants" :key="r.id" :restaurant="r" />
    </div>
  </div>
</template>
