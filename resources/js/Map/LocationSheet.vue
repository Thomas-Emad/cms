<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CATEGORY_META } from './categories';
import type { MapLocation } from './types';

defineProps<{
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

const btn = 'flex h-14 items-center justify-center rounded-full px-7 text-lg font-semibold transition-transform duration-150 active:scale-95';
</script>

<template>
    <article class="glass overflow-hidden rounded-[32px]" data-testid="location-sheet" :aria-label="loc.name">
        <!-- image: grows when expanded -->
        <div class="relative overflow-hidden transition-[height] duration-500" :style="{ height: expanded ? '15rem' : '10rem', transitionTimingFunction: 'var(--ease-spring)' }">
            <img v-if="loc.image" :src="loc.image" :alt="loc.name" draggable="false" class="h-full w-full object-cover" />
            <div v-else class="flex h-full w-full items-center justify-center text-6xl text-white" :style="{ background: `linear-gradient(135deg, ${CATEGORY_META[loc.category].color}, #183c2d)` }">
                {{ CATEGORY_META[loc.category].icon }}
            </div>
            <div class="absolute inset-0" style="background: linear-gradient(180deg, transparent 45%, rgba(0,0,0,0.45))" />
            <button type="button" class="absolute right-4 top-4 h-11 w-11 rounded-full bg-black/40 text-2xl leading-none text-white backdrop-blur-sm active:scale-90" aria-label="Close" data-testid="sheet-close" @click="emit('close')">×</button>
        </div>

        <div class="p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: #8a6f3c">{{ floorName }} · {{ CATEGORY_META[loc.category].label }}</p>
            <h2 class="mt-1 text-3xl leading-tight text-slate-900" style="font-family: var(--font-display)">{{ loc.name }}</h2>

            <p v-if="loc.description" class="mt-3 text-lg leading-relaxed text-slate-600" :class="{ 'line-clamp-2': !expanded }">{{ loc.description }}</p>

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

            <p v-if="loc.opening_hours && !expanded" class="mt-2 text-base text-slate-500">Open {{ loc.opening_hours }}</p>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <Link v-if="loc.details_url" :href="loc.details_url" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="view-details">View Details</Link>
                <button v-else type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="view-details" @click="emit('toggle')">
                    {{ expanded ? 'Show Less' : 'View Details' }}
                </button>
                <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="get-directions" @click="emit('directions')">Get Directions</button>
            </div>

            <button v-if="!isHere" type="button" class="mt-3 flex h-12 w-full items-center justify-center gap-2 rounded-full text-base font-medium text-[#2b6fd6] active:scale-95" data-testid="set-here" @click="emit('here')">
                <span aria-hidden="true">●</span> I'm here — set as my starting point
            </button>
            <p v-else class="mt-3 flex h-12 items-center justify-center gap-2 text-base font-medium text-[#2b6fd6]"><span aria-hidden="true">●</span> You are here</p>
        </div>
    </article>
</template>
