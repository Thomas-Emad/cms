<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import StoryPlayer from '@/Components/StoryPlayer.vue';

export interface ShowcaseItem {
    id: number;
    title: string;
    subtitle?: string | null;
    facts?: string | null;
    image?: string | null;
    href: string;
}

const props = withDefaults(
    defineProps<{ items: ShowcaseItem[]; heading: string; emptyText: string; actionLabel?: string }>(),
    { actionLabel: 'Explore' },
);

/**
 * Cinematic list: one item per "story" slide - big image, slow zoom, title
 * animating in - with an Explore button. Guests can switch to a plain grid.
 * Used by Rooms & Suites and Meeting Rooms.
 */
const view = ref<'story' | 'grid'>('story');
const slides = computed(() => props.items.map((i) => ({ url: i.image ?? null, type: 'image' as const, alt_text: i.title })));

const pill =
    'flex items-center h-12 px-6 rounded-full bg-black/40 text-white text-base uppercase tracking-wider active:scale-95 transition-transform backdrop-blur-sm';
</script>

<template>
    <div v-if="!items.length" class="mx-auto max-w-7xl px-10 pt-28 pb-16">
        <h1 class="text-5xl mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">{{ heading }}</h1>
        <p class="text-xl text-slate-400 mt-16 text-center">{{ emptyText }}</p>
    </div>

    <div v-else-if="view === 'story'" class="relative">
        <StoryPlayer :slides="slides" :interval-seconds="7" height="calc(100dvh - var(--kiosk-dock-h, 0px))" animate-overlay>
            <template #default="{ index }">
                <p class="text-sm uppercase tracking-[0.25em]" style="color: var(--luxury-champagne)">{{ heading }}</p>
                <h2 class="mt-3 text-white text-7xl leading-[1.05] max-w-4xl" style="font-family: var(--font-display)">{{ items[index].title }}</h2>
                <p v-if="items[index].facts" class="mt-4 text-white/80 text-xl uppercase tracking-wider">{{ items[index].facts }}</p>
                <p v-if="items[index].subtitle" class="mt-3 text-white/85 text-2xl max-w-3xl">{{ items[index].subtitle }}</p>
                <Link
                    :href="items[index].href"
                    class="pointer-events-auto mt-8 inline-flex items-center gap-3 h-16 px-10 rounded-full text-xl uppercase tracking-wider text-slate-900 active:scale-95 transition-transform"
                    style="background: var(--luxury-champagne)"
                >
                    {{ actionLabel }} <span class="rtl:rotate-180 inline-block">→</span>
                </Link>
            </template>
        </StoryPlayer>

        <button
            type="button"
            :class="pill"
            class="absolute end-10 z-30"
            style="top: calc(var(--kiosk-topbar-h, 0px) + 2.25rem)"
            data-testid="to-grid"
            @click="view = 'grid'"
        >
            ▦ {{ $t('common.see_all') }}
        </button>
    </div>

    <div v-else class="mx-auto max-w-7xl px-10 pt-28 pb-16">
        <div class="flex items-center justify-between mb-10">
            <h1 class="text-5xl" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">{{ heading }}</h1>
            <button type="button" class="flex items-center h-12 px-6 rounded-full border border-slate-300 text-slate-700 text-base uppercase tracking-wider active:scale-95 transition-transform" data-testid="to-story" @click="view = 'story'">
                ▶ {{ $t('common.story_view') }}
            </button>
        </div>

        <div class="grid grid-cols-2 xl:grid-cols-3 gap-5">
            <Link v-for="item in items" :key="item.id" :href="item.href" class="relative block aspect-[4/3] overflow-hidden active:scale-[0.99] transition-transform duration-150">
                <img v-if="item.image" :src="item.image" :alt="item.title" draggable="false" class="absolute inset-0 h-full w-full object-cover" />
                <div v-else class="absolute inset-0 bg-slate-200" />
                <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
                <div class="absolute inset-x-0 bottom-0 p-6">
                    <p v-if="item.facts" class="text-sm uppercase tracking-wide text-white/75">{{ item.facts }}</p>
                    <h3 class="mt-1 text-3xl text-white" style="font-family: var(--font-display)">{{ item.title }}</h3>
                </div>
            </Link>
        </div>
    </div>
</template>
