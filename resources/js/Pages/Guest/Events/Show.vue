<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { HotelEvent } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ event: HotelEvent }>();
</script>

<template>
    <div>
        <section class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <div class="absolute inset-0">
                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title" class="w-full h-full object-cover" />
                <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
            </div>
            <div class="relative z-10 w-full px-6 lg:px-10 pb-14">
                <div class="mx-auto max-w-7xl">
                    <p class="text-white/70 text-xs uppercase tracking-[0.2em] mb-2">
                        {{ event.start_date }}<span v-if="event.start_time"> · {{ event.start_time }}</span>
                    </p>
                    <h1 class="text-white text-5xl lg:text-6xl" style="font-family: var(--font-display)">{{ event.title }}</h1>
                    <p v-if="event.location" class="mt-3 text-white/85 text-lg">{{ event.location }}</p>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-3xl px-6 lg:px-10 py-16 lg:py-24">
            <p v-if="event.description" class="text-lg leading-relaxed text-slate-600 whitespace-pre-line">{{ event.description }}</p>
            <a
                v-if="event.booking_required && event.booking_url"
                :href="event.booking_url"
                class="group mt-8 inline-flex items-center gap-2 text-sm uppercase tracking-wide"
                style="color: var(--color-primary, #1f4b5a)"
            >
                <span class="border-b border-current/40 pb-1 group-hover:border-current transition-colors">Reserve your spot</span>
                <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
            </a>
        </div>
    </div>
</template>
