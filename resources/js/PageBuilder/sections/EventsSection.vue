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
    <section class="mx-auto max-w-5xl px-6 lg:px-10 py-16 lg:py-24">
        <h2
            v-if="props.title"
            class="reveal text-4xl lg:text-6xl leading-[1.05] mb-10"
            style="font-family: var(--font-display); color: var(--luxury-forest)"
            v-reveal
        >
            {{ props.title }}
        </h2>

        <div>
            <div v-for="(event, i) in data?.events ?? []" :key="event.id" class="reveal" v-reveal="{ delay: i * 50 }">
                <EventCard :event="event" />
            </div>
        </div>

        <p v-if="mode === 'edit' && !data?.events?.length" class="text-xs text-slate-400 mt-2">
            No {{ props.upcoming_only === false ? '' : 'upcoming ' }}events found.
        </p>
    </section>
</template>
