<script setup lang="ts">
import EventCard from '@/Components/Cards/EventCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import type { HotelEvent } from '@/types/content';

interface EventsProps {
  title?: string;
  limit?: number;
  upcoming_only?: boolean;
}
interface EventsData {
  events: HotelEvent[];
}

defineProps<{
  props: EventsProps;
  settings: SectionSettings;
  data?: EventsData;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-3">{{ props.title }}</h2>

    <div class="space-y-3">
      <EventCard v-for="event in data?.events ?? []" :key="event.id" :event="event" />
    </div>

    <p v-if="mode === 'edit' && !data?.events?.length" class="text-xs text-slate-400 mt-2">
      No {{ props.upcoming_only === false ? '' : 'upcoming ' }}events found.
    </p>
  </section>
</template>
