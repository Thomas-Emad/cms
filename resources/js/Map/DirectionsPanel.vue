<script setup lang="ts">
import { computed } from 'vue';
import { CATEGORY_META, STEP_ICON } from './categories';
import { formatDistance, formatDuration } from './routing';
import type { MapFloor, MapLocation, Origin, Route } from './types';

const props = defineProps<{
    mode: 'choosing' | 'preview' | 'navigating' | 'arrived';
    route: Route | null;
    origin: Origin | null;
    destination: MapLocation;
    stepIndex: number;
    error: string | null;
    floors: MapFloor[];
    simulating: boolean;
}>();
const emit = defineEmits<{
    (e: 'start'): void;
    (e: 'cancel'): void;
    (e: 'prev'): void;
    (e: 'next'): void;
    (e: 'goto', i: number): void;
    (e: 'swap'): void;
    (e: 'change-origin'): void;
    (e: 'pick-on-map'): void;
    (e: 'finish'): void;
    (e: 'simulate'): void;
}>();

const steps = computed(() => props.route?.steps ?? []);
const step = computed(() => steps.value[props.stepIndex]);
const nextStep = computed(() => steps.value[props.stepIndex + 1]);
const floorName = (id: string) => props.floors.find((f) => f.id === id)?.name ?? id;
const viaLift = computed(() => steps.value.find((s) => s.transition)?.transition);

const btn = 'flex h-14 items-center justify-center rounded-full px-7 text-lg font-semibold transition-transform duration-150 active:scale-95';
</script>

<template>
    <section class="glass overflow-hidden rounded-[32px]" data-testid="directions-panel" :data-mode="mode">
        <!-- ---------------- From / To (choosing + preview) ---------------- -->
        <header v-if="mode === 'choosing' || mode === 'preview'" class="p-5 pb-3">
            <div class="flex items-stretch gap-3">
                <div class="min-w-0 flex-1 space-y-2">
                    <button type="button" class="flex w-full items-center gap-3 rounded-2xl bg-black/5 px-4 py-3 text-left active:scale-[0.99]" data-testid="from-field" @click="emit('change-origin')">
                        <span class="h-3.5 w-3.5 shrink-0 rounded-full border-4 border-[#183c2d] bg-white" />
                        <span class="min-w-0"><span class="block text-xs uppercase tracking-wider text-slate-500">From</span>
                            <span class="block truncate text-lg font-medium text-slate-900">{{ origin?.label ?? 'Choose a starting point' }}</span></span>
                    </button>
                    <div class="flex w-full items-center gap-3 rounded-2xl bg-black/5 px-4 py-3">
                        <span class="h-3.5 w-3.5 shrink-0 rounded-full bg-[#b99a62]" />
                        <span class="min-w-0"><span class="block text-xs uppercase tracking-wider text-slate-500">To</span>
                            <span class="block truncate text-lg font-medium text-slate-900">{{ destination.name }}</span></span>
                    </div>
                </div>
                <button type="button" class="self-center h-12 w-12 shrink-0 rounded-full bg-black/5 text-xl text-[#183c2d] active:rotate-180 transition-transform duration-500" :disabled="!origin?.locationId" :class="{ 'opacity-30': !origin?.locationId }" aria-label="Swap start and destination" data-testid="swap" @click="emit('swap')">⇅</button>
            </div>
        </header>

        <!-- ---------------- choosing: no start known yet ---------------- -->
        <div v-if="mode === 'choosing'" class="space-y-3 px-5 pb-5">
            <p class="text-lg text-slate-600">Where are you starting from?</p>
            <button type="button" :class="btn" class="w-full bg-[#183c2d] text-white" data-testid="choose-origin" @click="emit('change-origin')">Search for a place</button>
            <button type="button" :class="btn" class="w-full border border-[#183c2d]/25 text-[#183c2d]" data-testid="pick-on-map" @click="emit('pick-on-map')">Tap my location on the map</button>
            <button type="button" class="h-12 w-full text-base text-slate-500" @click="emit('cancel')">Cancel</button>
        </div>

        <!-- ---------------- preview ---------------- -->
        <div v-else-if="mode === 'preview'" class="px-5 pb-5">
            <p v-if="error" class="rounded-2xl bg-red-50 px-4 py-4 text-lg text-red-700" data-testid="route-error">{{ error }}</p>
            <template v-else-if="route">
                <div class="flex items-end justify-between">
                    <p class="text-4xl font-semibold text-slate-900" data-testid="summary">{{ formatDistance(route.meters) || '0 m' }} <span class="text-2xl font-normal text-slate-500">· {{ formatDuration(route.seconds) }} walk</span></p>
                </div>
                <p v-if="viaLift" class="mt-1 text-base text-slate-500">
                    {{ viaLift.kind === 'stairs' ? 'Stairs' : 'Elevator' }} {{ viaLift.direction }} to the {{ floorName(viaLift.toFloor) }}
                </p>

                <ol class="mt-4 max-h-56 space-y-1 overflow-y-auto pr-1">
                    <li v-for="s in steps" :key="s.index">
                        <button type="button" class="flex w-full items-center gap-4 rounded-2xl px-3 py-2.5 text-left active:bg-black/5" @click="emit('goto', s.index)">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#183c2d] text-xl text-white">{{ STEP_ICON[s.kind] }}</span>
                            <span class="min-w-0 flex-1 text-lg text-slate-800">{{ s.text }}</span>
                            <span class="shrink-0 text-base tabular-nums text-slate-500">{{ formatDistance(s.meters) }}</span>
                        </button>
                    </li>
                </ol>

                <div class="mt-4 grid grid-cols-[1fr_2fr] gap-3">
                    <button type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="cancel" @click="emit('cancel')">Cancel</button>
                    <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="start" @click="emit('start')">Start</button>
                </div>
            </template>
            <button v-if="error" type="button" :class="btn" class="mt-3 w-full border border-[#183c2d]/25 text-[#183c2d]" @click="emit('cancel')">Back</button>
        </div>

        <!-- ---------------- active navigation ---------------- -->
        <div v-else-if="mode === 'navigating' && step" class="p-5">
            <div class="flex items-start gap-4">
                <span class="nav-icon flex h-20 w-20 shrink-0 items-center justify-center rounded-[26px] bg-[#183c2d] text-5xl text-white" :key="step.index" data-testid="step-icon">{{ STEP_ICON[step.kind] }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#8a6f3c]">Step {{ stepIndex + 1 }} of {{ steps.length }}</p>
                    <h2 class="text-3xl font-semibold leading-tight text-slate-900" data-testid="step-text">{{ step.text }}<span v-if="step.meters >= 1" class="text-slate-400"> · {{ formatDistance(step.meters) }}</span></h2>
                </div>
            </div>
            <p v-if="step.detail" class="mt-3 text-lg leading-relaxed text-slate-600">{{ step.detail }}</p>

            <p v-if="nextStep" class="mt-4 flex items-center gap-3 rounded-2xl bg-black/5 px-4 py-3 text-base text-slate-600" data-testid="next-preview">
                <span class="text-xl text-[#183c2d]">{{ STEP_ICON[nextStep.kind] }}</span>
                <span><b class="text-slate-900">{{ nextStep.text }}</b><template v-if="step.meters >= 1"> in {{ formatDistance(step.meters) }}</template></span>
            </p>

            <div class="mt-4 flex justify-center gap-2" aria-hidden="true">
                <span v-for="s in steps" :key="s.index" class="h-2 rounded-full transition-all duration-500" :class="s.index === stepIndex ? 'w-8 bg-[#183c2d]' : s.index < stepIndex ? 'w-2 bg-[#183c2d]/50' : 'w-2 bg-black/15'" />
            </div>

            <div class="mt-4 grid grid-cols-[1fr_2fr] gap-3">
                <button type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d] disabled:opacity-30" :disabled="stepIndex === 0" data-testid="prev" @click="emit('prev')">Back</button>
                <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="next" @click="emit('next')">Next</button>
            </div>
            <div class="mt-2 flex items-center justify-between">
                <button type="button" class="h-12 px-4 text-base font-medium text-[#183c2d] active:scale-95" data-testid="simulate" @click="emit('simulate')">{{ simulating ? '❚❚ Pause walk' : '▶ Simulate walk' }}</button>
                <button type="button" class="h-12 px-4 text-base text-slate-500 active:scale-95" data-testid="end" @click="emit('cancel')">End</button>
            </div>
        </div>

        <!-- ---------------- arrival ---------------- -->
        <div v-else-if="mode === 'arrived'" class="p-6 text-center" data-testid="arrived">
            <div class="arrive-badge mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-[#14805e] text-6xl text-white">✓</div>
            <h2 class="mt-4 text-4xl text-slate-900" style="font-family: var(--font-display)">You've arrived</h2>
            <p class="mt-1 text-lg text-slate-600">{{ step?.detail ?? destination.name }}</p>
            <div class="mt-4 flex items-center gap-4 rounded-2xl bg-black/5 p-3 text-left">
                <img v-if="destination.image" :src="destination.image" alt="" class="h-16 w-16 rounded-xl object-cover" />
                <span v-else class="flex h-16 w-16 items-center justify-center rounded-xl text-3xl text-white" :style="{ background: CATEGORY_META[destination.category].color }">{{ CATEGORY_META[destination.category].icon }}</span>
                <span class="min-w-0"><span class="block truncate text-xl font-semibold text-slate-900">{{ destination.name }}</span>
                    <span class="block text-base text-slate-500">{{ floorName(destination.floor) }}<template v-if="destination.opening_hours"> · {{ destination.opening_hours }}</template></span></span>
            </div>
            <div class="mt-5 grid grid-cols-[1fr_2fr] gap-3">
                <button type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="prev" @click="emit('prev')">Back</button>
                <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="finish" @click="emit('finish')">Done</button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.nav-icon {
    animation: nav-icon-in 450ms var(--ease-spring) both;
}
@keyframes nav-icon-in {
    from { transform: scale(0.6) rotate(-12deg); opacity: 0; }
    to { transform: none; opacity: 1; }
}
.arrive-badge {
    animation: arrive 800ms var(--ease-spring) both;
    box-shadow: 0 0 0 0 rgba(20, 128, 94, 0.5);
}
@keyframes arrive {
    0% { transform: scale(0.3); box-shadow: 0 0 0 0 rgba(20, 128, 94, 0.55); }
    60% { transform: scale(1.12); }
    100% { transform: scale(1); box-shadow: 0 0 0 36px rgba(20, 128, 94, 0); }
}
@media (prefers-reduced-motion: reduce) {
    .nav-icon, .arrive-badge { animation: none; }
}
</style>
