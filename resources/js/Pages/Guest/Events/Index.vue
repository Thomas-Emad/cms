<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import EventCard from '@/Components/Cards/EventCard.vue';
import type { HotelEvent } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ events: HotelEvent[] }>();
</script>

<template>
  <div class="mx-auto max-w-screen-sm px-4 py-6">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Upcoming Events</h1>
    <div class="space-y-3">
      <EventCard v-for="event in events" :key="event.id" :event="event" />
    </div>
    <p v-if="!events.length" class="text-sm text-slate-400 mt-6 text-center">No upcoming events.</p>
  </div>
</template>
