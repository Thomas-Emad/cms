<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';
import { useTheme } from '@/composables/useTheme';
import type { Hotel } from '@/types/hotel';

defineOptions({ layout: GuestShell });

const props = defineProps<{
  hotel: Hotel;
}>();

// Phase 1: theme isn't passed down yet from the controller (added once
// ThemeResolver service lands alongside real content in Phase 2/4).
useTheme();
</script>

<template>
  <div class="mx-auto max-w-screen-md px-4 py-16 text-center">
    <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-3xl mb-6 shadow-xs border border-slate-200/50">
      🏨
    </div>
    <h1 class="text-3xl font-semibold text-slate-800 tracking-tight">
      Welcome to {{ hotel.name }}
    </h1>
    <p class="mt-3 text-slate-600 text-base max-w-lg mx-auto">
      Experience comfort and excellence. Explore our amenities, facilities, and services below.
    </p>

    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
      <a
        href="/facilities"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-2xs"
      >
        <span>Explore Facilities</span>
        <span>→</span>
      </a>
      <a
        href="/restaurants"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs"
      >
        <span>Dining & Restaurants</span>
      </a>
    </div>
  </div>
</template>
