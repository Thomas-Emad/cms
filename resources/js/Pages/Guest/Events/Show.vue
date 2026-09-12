<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { HotelEvent } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ event: HotelEvent }>();
</script>

<template>
  <div>
    <div class="aspect-[16/9] bg-slate-100">
      <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title" class="h-full w-full object-cover" />
    </div>
    <div class="mx-auto max-w-screen-sm px-4 py-6">
      <p class="text-sm text-slate-400">
        {{ event.start_date }}<span v-if="event.start_time"> · {{ event.start_time }}</span>
      </p>
      <h1 class="text-xl font-semibold text-slate-800">{{ event.title }}</h1>
      <p v-if="event.location" class="mt-1 text-sm text-slate-500">{{ event.location }}</p>
      <p v-if="event.description" class="mt-4 text-sm text-slate-600 whitespace-pre-line">{{ event.description }}</p>
      <a
        v-if="event.booking_required && event.booking_url"
        :href="event.booking_url"
        class="mt-6 inline-block rounded-md px-4 py-2 text-sm font-medium text-white"
        style="background: var(--color-primary, #1F4B5A)"
      >
        Reserve your spot
      </a>
    </div>
  </div>
</template>
