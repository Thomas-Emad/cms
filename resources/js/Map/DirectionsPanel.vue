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

const btn = 'flex h-12 items-center justify-center rounded-full px-4 text-base font-semibold transition-transform duration-150 active:scale-95';
</script>

<template>
    <!-- Header and buttons stay put; the middle scrolls when the screen is short. -->
    <section class="glass flex max-h-full min-h-0 flex-col overflow-hidden rounded-3xl" data-testid="directions-panel" :data-mode="mode">
        <!-- ---------------- From / To (choosing + preview) ---------------- -->
        <header v-if="mode === 'choosing' || mode === 'preview'" class="shrink-0 p-4 pb-2">
            <div class="flex items-stretch gap-3">
                <div class="min-w-0 flex-1 space-y-2">
                    <button type="button" class="flex w-full items-center gap-3 rounded-2xl bg-black/5 px-3.5 py-2 text-start active:scale-[0.99]" data-testid="from-field" @click="emit('change-origin')">
                        <span class="h-3.5 w-3.5 shrink-0 rounded-full border-4 border-[#183c2d] bg-white" />
                        <span class="min-w-0"><span class="block text-xs uppercase tracking-wider text-slate-500">{{ $t('map.from') }}</span>
                            <span class="block truncate text-base font-medium text-slate-900">{{ origin?.label ?? $t('map.choose_start') }}</span></span>
                    </button>
                    <div class="flex w-full items-center gap-3 rounded-2xl bg-black/5 px-3.5 py-2">
                        <span class="h-3.5 w-3.5 shrink-0 rounded-full bg-[#b99a62]" />
                        <span class="min-w-0"><span class="block text-xs uppercase tracking-wider text-slate-500">{{ $t('map.to') }}</span>
                            <span class="block truncate text-base font-medium text-slate-900">{{ destination.name }}</span></span>
                    </div>
                </div>
                <button type="button" class="self-center h-10 w-10 shrink-0 rounded-full bg-black/5 text-lg text-[#183c2d] active:rotate-180 transition-transform duration-500" :disabled="!origin?.locationId" :class="{ 'opacity-30': !origin?.locationId }" :aria-label="$t('map.swap')" data-testid="swap" @click="emit('swap')">⇅</button>
            </div>
        </header>

        <!-- ---------------- choosing: no start known yet ---------------- -->
        <div v-if="mode === 'choosing'" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-5 pb-5">
            <p class="text-lg text-slate-600">{{ $t('map.starting_question') }}</p>
            <button type="button" :class="btn" class="w-full bg-[#183c2d] text-white" data-testid="choose-origin" @click="emit('change-origin')">{{ $t('map.search_place') }}</button>
            <button type="button" :class="btn" class="w-full border border-[#183c2d]/25 text-[#183c2d]" data-testid="pick-on-map" @click="emit('pick-on-map')">{{ $t('map.tap_map') }}</button>
            <button type="button" class="h-12 w-full text-base text-slate-500" @click="emit('cancel')">{{ $t('common.cancel') }}</button>
        </div>

        <!-- ---------------- preview ---------------- -->
        <template v-else-if="mode === 'preview'">
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4">
                <p v-if="error" class="rounded-2xl bg-red-50 px-4 py-4 text-lg text-red-700" data-testid="route-error">{{ error }}</p>
                <template v-else-if="route">
                    <p class="text-4xl font-semibold text-slate-900" data-testid="summary">{{ formatDistance(route.meters) || '0 m' }} <span class="text-2xl font-normal text-slate-500">· {{ formatDuration(route.seconds) }} {{ $t('map.walk') }}</span></p>
                    <p v-if="viaLift" class="mt-1 text-base text-slate-500">
                        {{ viaLift.kind === 'stairs' ? $t('map.stairs') : $t('map.elevator') }} {{ viaLift.direction === 'up' ? $t('map.up') : $t('map.down') }} {{ floorName(viaLift.toFloor) }}
                    </p>
                    <ol class="mt-3 space-y-1 pb-2">
                        <li v-for="s in steps" :key="s.index">
                            <button type="button" class="flex w-full items-center gap-4 rounded-2xl px-3 py-2 text-start active:bg-black/5" @click="emit('goto', s.index)">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#183c2d] text-base text-white">{{ STEP_ICON[s.kind] }}</span>
                                <span class="min-w-0 flex-1 text-base text-slate-800">{{ s.text }}</span>
                                <span class="shrink-0 text-base tabular-nums text-slate-500">{{ formatDistance(s.meters) }}</span>
                            </button>
                        </li>
                    </ol>
                </template>
            </div>
            <div class="shrink-0 px-4 pb-4 pt-2">
                <div v-if="!error" class="grid grid-cols-[1fr_2fr] gap-3">
                    <button type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="cancel" @click="emit('cancel')">{{ $t('common.cancel') }}</button>
                    <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="start" @click="emit('start')">{{ $t('map.start_nav') }}</button>
                </div>
                <button v-else type="button" :class="btn" class="w-full border border-[#183c2d]/25 text-[#183c2d]" @click="emit('cancel')">{{ $t('common.back') }}</button>
            </div>
        </template>

        <!-- ---------------- active navigation ---------------- -->
        <template v-else-if="mode === 'navigating' && step">
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 pb-2">
                <div class="flex items-start gap-4">
                    <span class="nav-icon flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#183c2d] text-3xl text-white" :key="step.index" data-testid="step-icon">{{ STEP_ICON[step.kind] }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#8a6f3c]">{{ stepIndex + 1 }} / {{ steps.length }}</p>
                        <h2 class="text-3xl font-semibold leading-tight text-slate-900" data-testid="step-text">{{ step.text }}<span v-if="step.meters >= 1" class="text-slate-400"> · {{ formatDistance(step.meters) }}</span></h2>
                    </div>
                </div>
                <p v-if="step.detail" class="mt-2 text-base leading-relaxed text-slate-600">{{ step.detail }}</p>

                <p v-if="nextStep" class="mt-3 flex items-center gap-3 rounded-2xl bg-black/5 px-3.5 py-2.5 text-sm text-slate-600" data-testid="next-preview">
                    <span class="text-xl text-[#183c2d]">{{ STEP_ICON[nextStep.kind] }}</span>
                    <span><b class="text-slate-900">{{ nextStep.text }}</b><template v-if="step.meters >= 1"> {{ $t('map.in_distance', { distance: formatDistance(step.meters) }) }}</template></span>
                </p>

                <div class="mt-4 flex justify-center gap-2" aria-hidden="true">
                    <span v-for="s in steps" :key="s.index" class="h-2 rounded-full transition-all duration-500" :class="s.index === stepIndex ? 'w-8 bg-[#183c2d]' : s.index < stepIndex ? 'w-2 bg-[#183c2d]/50' : 'w-2 bg-black/15'" />
                </div>
            </div>
            <div class="shrink-0 px-4 pb-3 pt-2">
                <div class="grid grid-cols-[1fr_2fr] gap-3">
                    <button type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d] disabled:opacity-30" :disabled="stepIndex === 0" data-testid="prev" @click="emit('prev')">{{ $t('common.back') }}</button>
                    <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="next" @click="emit('next')">{{ $t('common.next') }}</button>
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <button type="button" class="h-10 px-3 text-sm font-medium text-[#183c2d] active:scale-95" data-testid="simulate" @click="emit('simulate')">{{ simulating ? '❚❚' : '▶' }}</button>
                    <button type="button" class="h-10 px-3 text-sm text-slate-500 active:scale-95" data-testid="end" @click="emit('cancel')">{{ $t('common.cancel') }}</button>
                </div>
            </div>
        </template>

        <!-- ---------------- arrival ---------------- -->
        <template v-else-if="mode === 'arrived'">
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-5 pb-2 text-center" data-testid="arrived">
                <div class="arrive-badge mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#14805e] text-4xl text-white">✓</div>
                <h2 class="mt-2 text-3xl text-slate-900" style="font-family: var(--font-display)">{{ $t('common.arrived') }}</h2>
                <p class="mt-1 text-base text-slate-600">{{ step?.detail ?? destination.name }}</p>
                <div class="mt-4 flex items-center gap-4 rounded-2xl bg-black/5 p-3 text-start">
                    <img v-if="destination.image" :src="destination.image" alt="" class="h-14 w-14 rounded-xl object-cover" />
                    <span v-else class="flex h-14 w-14 items-center justify-center rounded-xl text-2xl text-white" :style="{ background: CATEGORY_META[destination.category].color }">{{ CATEGORY_META[destination.category].icon }}</span>
                    <span class="min-w-0"><span class="block truncate text-lg font-semibold text-slate-900">{{ destination.name }}</span>
                        <span class="block text-sm text-slate-500">{{ floorName(destination.floor) }}<template v-if="destination.opening_hours"> · {{ destination.opening_hours }}</template></span></span>
                </div>
            </div>
            <div class="shrink-0 px-5 pb-4 pt-2">
                <div class="grid grid-cols-[1fr_2fr] gap-3">
                    <button type="button" :class="btn" class="border border-[#183c2d]/25 text-[#183c2d]" data-testid="prev" @click="emit('prev')">{{ $t('common.back') }}</button>
                    <button type="button" :class="btn" class="bg-[#183c2d] text-white" data-testid="finish" @click="emit('finish')">{{ $t('common.done') }}</button>
                </div>
            </div>
        </template>
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
