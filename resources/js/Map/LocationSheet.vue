<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CATEGORY_META } from './categories';
import type { MapLocation } from './types';

const props = defineProps<{
    loc: MapLocation;
    floorName: string;
    expanded: boolean;
    isHere: boolean;
}>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'toggle'): void;
    (e: 'directions'): void;
    (e: 'here'): void;
}>();

// A page on this hotel's site opens inside the app; a full web address opens in a new tab.
const detailsUrl = computed(() => props.loc.details_url || null);
const internal = computed(() => !!detailsUrl.value && detailsUrl.value.startsWith('/'));

const btn = 'flex h-12 items-center justify-center rounded-full px-4 text-base font-semibold transition-transform duration-150 active:scale-95';
</script>

<template>
    <!-- Layout: the photo shrinks and the text scrolls when the screen is short, so the buttons are ALWAYS on screen. -->
    <article class="glass flex max-h-full min-h-0 flex-col overflow-hidden rounded-3xl" data-testid="location-sheet" :aria-label="loc.name">
        <div class="relative min-h-[72px] overflow-hidden transition-[flex-basis] duration-500" :style="{ flex: `0 1 ${expanded ? '210px' : '130px'}`, transitionTimingFunction: 'var(--ease-spring)' }">
            <img v-if="loc.image" :src="loc.image" :alt="loc.name" draggable="false" class="h-full w-full object-cover" />
            <div v-else class="flex h-full w-full items-center justify-center text-5xl text-white" :style="{ background: `linear-gradient(135deg, ${CATEGORY_META[loc.category].color}, #183c2d)` }">
                {{ CATEGORY_META[loc.category].icon }}
            </div>
            <div class="absolute inset-0" style="background: linear-gradient(180deg, transparent 45%, rgba(0,0,0,0.45))" />
            <button type="button" class="absolute right-3 top-3 h-10 w-10 rounded-full bg-black/40 text-xl leading-none text-white backdrop-blur-sm active:scale-90" aria-label="Close" data-testid="sheet-close" @click="emit('close')">×</button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 pt-4" data-testid="sheet-body">
            <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: #8a6f3c">{{ floorName }} · {{ CATEGORY_META[loc.category].label }}</p>
            <h2 class="mt-0.5 text-2xl leading-tight text-slate-900" style="font-family: var(--font-display)">{{ loc.name }}</h2>

            <p v-if="loc.description" class="mt-2 text-base leading-relaxed text-slate-600" :class="{ 'line-clamp-2': !expanded }">{{ loc.description }}</p>

            <div class="expander" :class="{ 'expander--open': expanded }">
                <div>
                    <dl class="space-y-2 pt-4 text-base">
                        <div v-if="loc.opening_hours" class="flex justify-between gap-4 border-t border-black/5 pt-3">
                            <dt class="text-slate-500">Opening hours</dt>
                            <dd class="font-medium text-slate-800">{{ loc.opening_hours }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-black/5 pt-3">
                            <dt class="text-slate-500">Location</dt>
                            <dd class="font-medium text-slate-800">{{ floorName }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <p v-if="loc.opening_hours && !expanded" class="mt-2 pb-2 text-base text-slate-500">Open {{ loc.opening_hours }}</p>
            <div class="h-2" />
        </div>

        <!-- pinned actions -->
        <div class="shrink-0 px-5 pb-3 pt-2">
            <div class="grid grid-cols-2 gap-3">
                <Link v-if="internal" :href="detailsUrl!" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="view-details" data-kind="page">View Details</Link>
                <a v-else-if="detailsUrl" :href="detailsUrl" target="_blank" rel="noopener" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="view-details" data-kind="external">View Details</a>
                <button v-else type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="view-details" data-kind="expand" @click="emit('toggle')">
                    {{ expanded ? 'Show Less' : 'More info' }}
                </button>
                <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="get-directions" @click="emit('directions')">Get Directions</button>
            </div>

            <button v-if="!isHere" type="button" class="mt-1 flex h-10 w-full items-center justify-center gap-2 rounded-full text-sm font-medium text-[#2b6fd6] active:scale-95" data-testid="set-here" @click="emit('here')">
                <span aria-hidden="true">●</span> I'm here — set as my starting point
            </button>
            <p v-else class="mt-1 flex h-10 items-center justify-center gap-2 text-sm font-medium text-[#2b6fd6]"><span aria-hidden="true">●</span> You are here</p>
        </div>
    </article>
</template>
