<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';
import EventCard from '@/Components/Cards/EventCard.vue';
import type { HotelEvent } from '@/types/content';

defineOptions({ layout: GuestShell });

defineProps<{ events: HotelEvent[] }>();
</script>

<template>
    <div class="mx-auto max-w-4xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
        <h1 class="reveal text-3xl lg:text-4xl mb-8" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>
            Upcoming Events
        </h1>
        <div>
            <div v-for="(event, i) in events" :key="event.id" class="reveal" v-reveal="{ delay: i * 50 }">
                <EventCard :event="event" />
            </div>
        </div>
        <p v-if="!events.length" class="text-sm text-slate-400 mt-10 text-center">No upcoming events.</p>
    </div>
</template>
