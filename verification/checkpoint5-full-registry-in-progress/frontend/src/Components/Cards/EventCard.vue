<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { HotelEvent } from '@/types/content';

defineProps<{
  event: Pick<HotelEvent, 'title' | 'slug' | 'start_date' | 'start_time' | 'location' | 'cover_image_url'>;
}>();
</script>

<template>
  <Link
    :href="`/events/${event.slug}`"
    class="flex gap-3 overflow-hidden bg-white shadow-sm p-2"
    style="border-radius: var(--radius, 8px)"
  >
    <div class="h-16 w-16 shrink-0 rounded-md bg-slate-100 overflow-hidden">
      <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title" class="h-full w-full object-cover" />
    </div>
    <div>
      <p class="text-xs text-slate-400">{{ event.start_date }}<span v-if="event.start_time"> · {{ event.start_time }}</span></p>
      <h3 class="font-medium text-slate-800">{{ event.title }}</h3>
      <p v-if="event.location" class="text-sm text-slate-500">{{ event.location }}</p>
    </div>
  </Link>
</template>
