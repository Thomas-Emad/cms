<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Offer } from '@/types/content';

defineProps<{
  offer: Pick<Offer, 'title' | 'slug' | 'price' | 'discount' | 'cover_image_url'>;
}>();
</script>

<template>
  <Link
    :href="`/offers/${offer.slug}`"
    class="block overflow-hidden bg-white shadow-sm"
    style="border-radius: var(--radius, 8px)"
  >
    <div class="relative aspect-[4/3] bg-slate-100">
      <img v-if="offer.cover_image_url" :src="offer.cover_image_url" :alt="offer.title" class="h-full w-full object-cover" />
      <span
        v-if="offer.discount"
        class="absolute top-2 right-2 rounded-full px-2 py-0.5 text-xs font-semibold text-white"
        style="background: var(--color-secondary, #D4AF37)"
      >
        -{{ offer.discount }}%
      </span>
    </div>
    <div class="p-3">
      <h3 class="font-medium text-slate-800">{{ offer.title }}</h3>
      <p v-if="offer.price" class="mt-0.5 text-sm text-slate-500">From ${{ offer.price }}</p>
    </div>
  </Link>
</template>
