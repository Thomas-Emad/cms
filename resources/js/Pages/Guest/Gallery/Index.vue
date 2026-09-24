<script setup lang="ts">
import { ref } from 'vue';
import GuestShell from '@/Layouts/GuestShell.vue';
import GalleryGrid from '@/Components/GalleryGrid.vue';
import StoryPlayer from '@/Components/StoryPlayer.vue';
import type { MediaItem } from '@/types/room';

defineOptions({ layout: GuestShell });

defineProps<{ images: MediaItem[] }>();

// Default is the cinematic story; "See all" switches to a tappable grid.
const view = ref<'story' | 'grid'>('story');
</script>

<template>
    <div v-if="!images.length" class="mx-auto max-w-7xl px-10 pt-28 pb-16">
        <h1 class="text-5xl mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">{{ $t('gallery.title') }}</h1>
        <p class="text-xl text-slate-400 mt-16 text-center">{{ $t('gallery.empty') }}</p>
    </div>

    <div v-else-if="view === 'story'" class="relative">
        <StoryPlayer :slides="images" :interval-seconds="5" height="calc(100dvh - var(--kiosk-dock-h, 0px))">
            <template #default="{ index, count }">
                <p class="text-sm uppercase tracking-[0.25em]" style="color: var(--luxury-champagne)">{{ $t('gallery.title') }}</p>
                <p class="mt-2 text-white/80 text-xl tabular-nums">{{ index + 1 }} / {{ count }}</p>
                <p class="mt-4 text-white/55 text-base uppercase tracking-[0.2em]">{{ $t('gallery.hint') }}</p>
            </template>
        </StoryPlayer>

        <button
            type="button"
            class="absolute end-10 z-30 flex items-center h-12 px-6 rounded-full bg-black/40 text-white text-base uppercase tracking-wider active:scale-95 transition-transform backdrop-blur-sm"
            style="top: calc(var(--kiosk-topbar-h, 0px) + 2.25rem)"
            data-testid="to-grid"
            @click="view = 'grid'"
        >
            ▦ {{ $t('common.see_all') }}
        </button>
    </div>

    <div v-else class="mx-auto max-w-7xl px-10 pt-28 pb-16">
        <div class="flex items-center justify-between mb-10">
            <h1 class="text-5xl" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">{{ $t('gallery.title') }}</h1>
            <button type="button" class="flex items-center h-12 px-6 rounded-full border border-slate-300 text-slate-700 text-base uppercase tracking-wider active:scale-95 transition-transform" data-testid="to-story" @click="view = 'story'">
                ▶ {{ $t('common.story_view') }}
            </button>
        </div>
        <GalleryGrid :images="images" />
    </div>
</template>
