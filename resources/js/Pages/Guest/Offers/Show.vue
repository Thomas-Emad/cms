<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { Offer } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ offer: Offer }>();
</script>

<template>
  <div>
    <div class="aspect-[16/9] bg-slate-100">
      <img v-if="offer.cover_image_url" :src="offer.cover_image_url" :alt="offer.title" class="h-full w-full object-cover" />
    </div>
    <div class="mx-auto max-w-screen-sm px-4 py-6">
      <h1 class="text-xl font-semibold text-slate-800">{{ offer.title }}</h1>
      <p v-if="offer.price" class="mt-1 text-slate-600">From ${{ offer.price }}<span v-if="offer.discount"> · Save {{ offer.discount }}%</span></p>
      <p v-if="offer.description" class="mt-4 text-sm text-slate-600 whitespace-pre-line">{{ offer.description }}</p>
      <p v-if="offer.valid_until" class="mt-4 text-xs text-slate-400">Valid until {{ offer.valid_until }}</p>
      <a
        v-if="offer.booking_url"
        :href="offer.booking_url"
        class="mt-6 inline-block rounded-md px-4 py-2 text-sm font-medium text-white"
        style="background: var(--color-primary, #1F4B5A)"
      >
        Book this offer
      </a>
    </div>
  </div>
</template>
