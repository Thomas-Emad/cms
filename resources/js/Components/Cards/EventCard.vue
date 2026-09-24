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
        class="group flex gap-5 items-center py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/60 transition-colors -mx-2 px-2 rounded-md"
    >
        <div class="h-20 w-20 shrink-0 overflow-hidden bg-slate-100 rounded-sm">
            <img
                v-if="event.cover_image_url"
                :src="event.cover_image_url"
                :alt="event.title"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
        </div>
        <div class="min-w-0">
            <p class="text-xs uppercase tracking-wide text-slate-400">
                {{ event.start_date }}<span v-if="event.start_time"> · {{ event.start_time }}</span>
            </p>
            <h3 class="mt-0.5 text-lg" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">
                {{ event.title }}
            </h3>
            <p v-if="event.location" class="text-sm text-slate-500">{{ event.location }}</p>
        </div>
        <span class="ms-auto shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 rtl:rotate-180 inline-block">→</span>
    </Link>
</template>
