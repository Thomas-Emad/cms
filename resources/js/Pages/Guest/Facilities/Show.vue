<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';
import StoryPlayer from '@/Components/StoryPlayer.vue';
import { computed } from 'vue';
import type { Facility } from '@/types/facility';
import type { MediaItem } from '@/types/room';

defineOptions({ layout: GuestShell });

const props = defineProps<{
    facility: Facility & { gallery_urls?: string[] };
    slides?: MediaItem[];
}>();

// Cover + gallery photos/videos play as a story; a lone cover stays a still.
const playStory = computed(() => (props.slides?.length ?? 0) > 1);

// Guests reach this from a list; the screen has no browser back button.
const back = () => window.history.back();
</script>

<template>
    <div>
        <section class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <StoryPlayer v-if="playStory" class="absolute inset-0" :slides="slides!" :interval-seconds="6" height="100%" />
            <div v-else class="absolute inset-0">
                <img
                    v-if="facility.cover_image_url"
                    :src="facility.cover_image_url"
                    :alt="facility.name"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
            </div>
            <button
                type="button"
                class="absolute left-10 z-20 flex items-center h-12 px-6 rounded-full bg-black/40 text-white text-base uppercase tracking-wider active:scale-95 transition-transform"
                style="top: calc(var(--kiosk-topbar-h, 0px) + 1rem)"
                @click="back"
            >
                ← Back
            </button>
            <div class="relative z-10 w-full px-6 lg:px-10 pb-14 pointer-events-none">
                <div class="mx-auto max-w-7xl">
                    <p v-if="facility.category" class="text-white/70 text-xs uppercase tracking-[0.2em] mb-2">{{ facility.category }}</p>
                    <h1 class="text-white text-5xl lg:text-6xl" style="font-family: var(--font-display)">{{ facility.name }}</h1>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-6 lg:px-10 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <p
                    v-if="facility.description"
                    class="lg:col-span-7 text-xl leading-relaxed text-slate-700 whitespace-pre-line"
                    style="font-family: var(--font-display)"
                >
                    {{ facility.description }}
                </p>

                <div class="lg:col-span-4 lg:col-start-9 space-y-5 text-sm">
                    <div v-if="facility.building">
                        <p class="text-slate-400 uppercase tracking-wide text-xs">Location</p>
                        <p class="mt-1 text-slate-700">
                            {{ [facility.building, facility.floor, facility.wing].filter(Boolean).join(', ') }}
                        </p>
                    </div>
                    <div v-if="facility.phone">
                        <p class="text-slate-400 uppercase tracking-wide text-xs">Phone</p>
                        <p class="mt-1 text-slate-700">{{ facility.phone }}</p>
                    </div>
                </div>
            </div>

            <ul v-if="facility.amenities?.length" class="mt-10 flex flex-wrap gap-2">
                <li
                    v-for="amenity in facility.amenities"
                    :key="amenity"
                    class="rounded-full border border-slate-200 px-4 py-1.5 text-xs uppercase tracking-wide text-slate-600"
                >
                    {{ amenity }}
                </li>
            </ul>
        </div>
    </div>
</template>
