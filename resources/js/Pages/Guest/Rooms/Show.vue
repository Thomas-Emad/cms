<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GalleryGrid from '@/Components/GalleryGrid.vue';
import type { MediaItem, Room } from '@/types/room';

defineOptions({ layout: GuestLayout });

const props = defineProps<{ room: Room & { gallery: MediaItem[] } }>();

const facts = computed(() =>
    [
        { label: 'Size', value: props.room.size_sqm ? `${props.room.size_sqm} m²` : null },
        { label: 'Guests', value: props.room.max_guests ? String(props.room.max_guests) : null },
        { label: 'Bed', value: props.room.bed_type },
        { label: 'View', value: props.room.view },
    ].filter((f) => f.value),
);
</script>

<template>
    <div>
        <section class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <div class="absolute inset-0">
                <img v-if="room.cover_image_url" :src="room.cover_image_url" :alt="room.name" draggable="false" class="w-full h-full object-cover" />
                <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
            </div>

            <Link
                href="/rooms"
                class="absolute left-10 z-20 flex items-center h-12 px-6 rounded-full bg-black/40 text-white text-base uppercase tracking-wider active:scale-95 transition-transform"
                style="top: calc(var(--kiosk-topbar-h, 0px) + 1rem)"
            >
                ← All rooms
            </Link>

            <div class="relative z-10 w-full px-10 pb-14">
                <div class="mx-auto max-w-7xl">
                    <h1 class="text-white text-6xl" style="font-family: var(--font-display)">{{ room.name }}</h1>
                    <p v-if="room.short_description" class="mt-3 text-white/85 text-2xl max-w-3xl">{{ room.short_description }}</p>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-10 py-16">
            <dl v-if="facts.length" class="flex flex-wrap gap-x-14 gap-y-6 pb-10 mb-10 border-b border-slate-200">
                <div v-for="f in facts" :key="f.label">
                    <dt class="text-sm uppercase tracking-[0.2em] text-slate-400">{{ f.label }}</dt>
                    <dd class="mt-1 text-3xl text-slate-800" style="font-family: var(--font-display)">{{ f.value }}</dd>
                </div>
            </dl>

            <p v-if="room.description" class="text-2xl leading-relaxed text-slate-700 max-w-4xl whitespace-pre-line" style="font-family: var(--font-display)">
                {{ room.description }}
            </p>

            <ul v-if="room.features?.length" class="mt-10 flex flex-wrap gap-3">
                <li v-for="feature in room.features" :key="feature" class="rounded-full border border-slate-200 px-5 py-2 text-base uppercase tracking-wide text-slate-600">
                    {{ feature }}
                </li>
            </ul>

            <div v-if="room.gallery.length" class="mt-16">
                <h2 class="text-3xl mb-6" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">Gallery</h2>
                <GalleryGrid :images="room.gallery" />
            </div>
        </div>
    </div>
</template>
