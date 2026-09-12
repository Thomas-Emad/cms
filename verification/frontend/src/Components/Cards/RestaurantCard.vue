<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Restaurant } from '@/types/restaurant';

defineProps<{
  restaurant: Pick<Restaurant, 'name' | 'slug' | 'cuisine' | 'location' | 'cover_image_url'>;
}>();
</script>

<template>
  <Link
    :href="`/restaurants/${restaurant.slug}`"
    class="block overflow-hidden bg-white shadow-sm"
    style="border-radius: var(--radius, 8px)"
  >
    <div class="aspect-[4/3] bg-slate-100">
      <img
        v-if="restaurant.cover_image_url"
        :src="restaurant.cover_image_url"
        :alt="restaurant.name"
        class="h-full w-full object-cover"
      />
    </div>
    <div class="p-3">
      <p v-if="restaurant.cuisine" class="text-xs uppercase tracking-wide text-slate-400">
        {{ restaurant.cuisine }}
      </p>
      <h3 class="font-medium text-slate-800">{{ restaurant.name }}</h3>
      <p v-if="restaurant.location" class="mt-0.5 text-sm text-slate-500">{{ restaurant.location }}</p>
    </div>
  </Link>
</template>
