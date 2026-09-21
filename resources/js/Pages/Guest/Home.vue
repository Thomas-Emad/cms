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
  <div class="mx-auto max-w-screen-sm px-4 py-10 text-center">
    <h1 class="text-2xl font-semibold" style="color: var(--color-primary, #1F4B5A)">
      Welcome to {{ hotel.name }}
    </h1>
    <p class="mt-2 text-slate-500 text-sm">
      Guest homepage — this will become a Page-Builder-driven page in Phase 3.
    </p>
  </div>
</template>
