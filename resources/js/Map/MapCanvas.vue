<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { Camera } from './camera';
import { AREA_STYLE, CATEGORY_META, DEST_COLOR, ROUTE_COLOR, YOU_COLOR } from './categories';
import type { HotelMapData, MapLocation, Origin, Point, Route } from './types';

const props = defineProps<{
    data: HotelMapData;
    floorId: string;
    floorDirection: 'up' | 'down';
    camera: Camera;
    selectedId: string | null;
    destinationId: string | null;
    routeOriginId: string | null;
    here: Origin | null;
    /** Live position while navigating (moves step by step). */
    you: { floor: string; x: number; y: number } | null;
    route: Route | null;
    routeActive: boolean;
    stepIndex: number | null;
    highlightIds: Set<string> | null;
    pickingStart: boolean;
}>();

const emit = defineEmits<{
    (e: 'select', id: string): void;
    (e: 'tap', p: Point): void;
}>();

const cam = props.camera;
const root = ref<HTMLElement | null>(null);

const floor = computed(() => props.data.floors.find((f) => f.id === props.floorId)!);
const locations = computed(() => props.data.locations.filter((l) => l.floor === props.floorId));
const k = computed(() => 1 / cam.zoom.value); // keeps markers a constant size on screen
const reduced = ref(false);

/* ------------------------------ measuring ------------------------------ */
let ro: ResizeObserver | null = null;
onMounted(() => {
    reduced.value = !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    ro = new ResizeObserver(([entry]) => {
        cam.width.value = entry.contentRect.width;
        cam.height.value = entry.contentRect.height;
    });
    if (root.value) ro.observe(root.value);
});
onBeforeUnmount(() => ro?.disconnect());

/* ------------------------------ gestures ------------------------------ */
const pointers = new Map<number, Point>();
let pinchDist = 0;
let moved = false;
let downAt = { x: 0, y: 0 };

function local(e: PointerEvent | WheelEvent) {
    const r = root.value!.getBoundingClientRect();
    return { x: e.clientX - r.left, y: e.clientY - r.top };
}

function onDown(e: PointerEvent) {
    pointers.set(e.pointerId, local(e));
    if (pointers.size === 1) {
        moved = false;
        downAt = local(e);
    }
    if (pointers.size === 2) {
        const [a, b] = [...pointers.values()];
        pinchDist = Math.hypot(a.x - b.x, a.y - b.y);
        moved = true; // a pinch is never a tap
    }
    cam.stop();
}

function onMove(e: PointerEvent) {
    const prev = pointers.get(e.pointerId);
    if (!prev) return;
    const cur = local(e);
    pointers.set(e.pointerId, cur);

    if (pointers.size === 1) {
        if (!moved && Math.hypot(cur.x - downAt.x, cur.y - downAt.y) > 6) moved = true;
        if (moved) cam.panByPx(cur.x - prev.x, cur.y - prev.y);
    } else if (pointers.size === 2) {
        const [a, b] = [...pointers.values()];
        const d = Math.hypot(a.x - b.x, a.y - b.y);
        if (pinchDist > 0) cam.zoomAt(d / pinchDist, (a.x + b.x) / 2, (a.y + b.y) / 2);
        pinchDist = d;
    }
}

function onUp(e: PointerEvent) {
    const wasTap = pointers.size === 1 && !moved;
    pointers.delete(e.pointerId);
    if (pointers.size < 2) pinchDist = 0;
    if (wasTap && !(e.target as Element).closest('[data-loc]')) emit('tap', cam.toWorld(local(e).x, local(e).y));
}

function onWheel(e: WheelEvent) {
    e.preventDefault();
    const p = local(e);
    cam.zoomAt(Math.exp(-e.deltaY * 0.0015), p.x, p.y);
}

function pick(loc: MapLocation) {
    if (moved) return; // it was a drag that ended on a marker
    emit('select', loc.id);
}

/* ------------------------------ marker state ------------------------------ */
const routeLocationIds = computed(() => new Set([props.destinationId, props.routeOriginId].filter(Boolean) as string[]));

function markerState(loc: MapLocation) {
    const selected = loc.id === props.selectedId;
    const dest = loc.id === props.destinationId;
    const inRoute = routeLocationIds.value.has(loc.id);
    let dim = false;
    if (props.routeActive) dim = !inRoute;
    else if (props.highlightIds) dim = !props.highlightIds.has(loc.id);
    const highlighted = !!props.highlightIds && props.highlightIds.has(loc.id) && !props.routeActive;
    return { selected, dest, inRoute, dim, highlighted };
}

function showLabel(loc: MapLocation) {
    const s = markerState(loc);
    if (s.selected || s.dest || s.inRoute || s.highlighted) return true;
    if (s.dim) return false;
    if (props.routeActive) return false;
    if (loc.category === 'room') return cam.zoom.value > fitZoom.value * 2.1;
    if (loc.category === 'transport' || loc.category === 'restroom') return cam.zoom.value > fitZoom.value * 1.4;
    return cam.zoom.value > fitZoom.value * 0.9;
}

const fitZoom = computed(() => cam.fitZoom.value || 1);

/* ------------------------------ route drawing ------------------------------ */
const segment = computed(() => props.route?.segments.find((s) => s.floor === props.floorId) ?? null);
const routeD = computed(() => (segment.value ? toPath(segment.value.points) : ''));
const legD = computed(() => {
    const s = props.stepIndex !== null ? props.route?.steps[props.stepIndex] : null;
    return s && s.floor === props.floorId && s.path.length > 1 ? toPath(s.path) : '';
});
const routeMeters = computed(() => {
    const pts = segment.value?.points ?? [];
    let d = 0;
    for (let i = 1; i < pts.length; i++) d += Math.hypot(pts[i].x - pts[i - 1].x, pts[i].y - pts[i - 1].y);
    return d * props.data.meters_per_unit;
});
const drawMs = computed(() => (reduced.value ? 0 : Math.min(2400, Math.max(700, 500 + routeMeters.value * 20))));
const drawn = ref(false);
const arrowsOn = ref(false);
let drawTimer: number | undefined;

function toPath(pts: Point[]) {
    return pts.map((p, i) => `${i ? 'L' : 'M'}${p.x} ${p.y}`).join(' ');
}

// (Re)start the draw-in animation whenever a new route appears or the floor changes.
watch(
    () => [props.route, props.floorId] as const,
    async () => {
        window.clearTimeout(drawTimer);
        drawn.value = false;
        arrowsOn.value = false;
        if (!segment.value) return;
        await nextTick();
        requestAnimationFrame(() => {
            drawn.value = true;
            drawTimer = window.setTimeout(() => (arrowsOn.value = !reduced.value), drawMs.value);
        });
    },
    { immediate: true },
);
onBeforeUnmount(() => window.clearTimeout(drawTimer));

const transitionsHere = computed(() => props.route?.transitions.filter((t) => t.floor === props.floorId) ?? []);
const arrivalsHere = computed(() =>
    (props.route?.transitions ?? []).filter((t) => t.toFloor === props.floorId).map((t) => ({ ...t, x: exitPoint(t).x, y: exitPoint(t).y })),
);
function exitPoint(t: { floor: string; toFloor: string; x: number; y: number }): Point {
    const seg = props.route?.segments.find((s) => s.floor === t.toFloor);
    return seg?.points[0] ?? { x: t.x, y: t.y };
}
const floorLabel = (id: string) => props.data.floors.find((f) => f.id === id)?.label ?? id;

/* ------------------------------ "you are here", smoothed ------------------------------ */
const youShown = computed(() => {
    const y = props.you ?? (props.here ? { floor: props.here.floor, x: props.here.x, y: props.here.y } : null);
    return y && y.floor === props.floorId ? y : null;
});
const youXY = ref<Point | null>(null);
let youRaf = 0;
watch(
    youShown,
    (t) => {
        cancelAnimationFrame(youRaf);
        if (!t) return (youXY.value = null);
        if (!youXY.value || reduced.value) return (youXY.value = { x: t.x, y: t.y });
        const step = () => {
            const c = youXY.value!;
            const dx = t.x - c.x;
            const dy = t.y - c.y;
            if (Math.hypot(dx, dy) < 0.3) return (youXY.value = { x: t.x, y: t.y });
            youXY.value = { x: c.x + dx * 0.12, y: c.y + dy * 0.12 };
            youRaf = requestAnimationFrame(step);
        };
        youRaf = requestAnimationFrame(step);
    },
    { immediate: true },
);
onBeforeUnmount(() => cancelAnimationFrame(youRaf));

const originPoint = computed(() => {
    if (!props.routeActive || !segment.value || props.route?.segments[0].floor !== props.floorId) return null;
    return segment.value.points[0];
});
const destLoc = computed(() => (props.destinationId ? props.data.locations.find((l) => l.id === props.destinationId) ?? null : null));
</script>

<template>
    <div
        ref="root"
        class="absolute inset-0 overflow-hidden touch-none select-none bg-[#e9e2d3]"
        :class="pickingStart ? 'cursor-crosshair' : 'cursor-grab active:cursor-grabbing'"
        data-testid="map-canvas"
        @pointerdown="onDown"
        @pointermove="onMove"
        @pointerup="onUp"
        @pointercancel="onUp"
        @wheel="onWheel"
    >
        <svg class="absolute inset-0 h-full w-full" :viewBox="cam.viewBox.value" preserveAspectRatio="none" role="application" :aria-label="`Map of ${floor.name}`">
            <defs>
                <filter id="map-glow" x="-30%" y="-30%" width="160%" height="160%">
                    <feGaussianBlur stdDeviation="4" result="b" />
                    <feMerge><feMergeNode in="b" /><feMergeNode in="SourceGraphic" /></feMerge>
                </filter>
            </defs>

            <Transition :name="floorDirection === 'up' ? 'floor-up' : 'floor-down'">
                <g :key="floorId" data-testid="floor-layer">
                    <image v-if="floor.plan_image" :href="floor.plan_image" x="0" y="0" :width="floor.width" :height="floor.height" preserveAspectRatio="none" />

                    <!-- floor plan; softened while a route is showing so the route reads first -->
                    <g class="map-areas" :class="{ 'map-areas--dim': routeActive }">
                        <rect
                            v-for="a in floor.areas"
                            :key="a.id"
                            :x="a.x" :y="a.y" :width="a.w" :height="a.h"
                            :rx="a.kind === 'building' ? 14 : a.kind === 'corridor' ? 0 : 6"
                            :fill="AREA_STYLE[a.kind].fill"
                            :stroke="AREA_STYLE[a.kind].stroke"
                            :stroke-width="a.kind === 'building' ? 3 : 1.5"
                        />
                        <line :x1="66" :x2="934" y1="300" y2="300" stroke="#d9cfb8" stroke-width="1.5" stroke-dasharray="6 8" />
                    </g>

                    <!-- route -->
                    <g v-if="segment" data-testid="route">
                        <path :d="routeD" fill="none" stroke="#fff" :stroke-width="12 * k" stroke-linecap="round" stroke-linejoin="round" opacity="0.9"
                              pathLength="1" :style="{ strokeDasharray: 1, strokeDashoffset: drawn ? 0 : 1, transition: `stroke-dashoffset ${drawMs}ms cubic-bezier(0.65,0,0.35,1)` }" />
                        <path :d="routeD" fill="none" :stroke="ROUTE_COLOR" :stroke-width="7 * k" stroke-linecap="round" stroke-linejoin="round"
                              :opacity="stepIndex !== null ? 0.4 : 1" pathLength="1" data-testid="route-line"
                              :style="{ strokeDasharray: 1, strokeDashoffset: drawn ? 0 : 1, transition: `stroke-dashoffset ${drawMs}ms cubic-bezier(0.65,0,0.35,1)` }" />
                        <path v-if="legD" :d="legD" fill="none" :stroke="ROUTE_COLOR" :stroke-width="9 * k" stroke-linecap="round" stroke-linejoin="round" filter="url(#map-glow)" data-testid="route-leg" />

                        <!-- direction arrows travelling along the route -->
                        <template v-if="arrowsOn">
                            <g v-for="n in 4" :key="n">
                                <g :transform="`scale(${k})`"><path d="M-6 -6 L4 0 L-6 6" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></g>
                                <animateMotion :path="routeD" :dur="`${Math.max(2.5, routeMeters / 12)}s`" :begin="`${(n * Math.max(2.5, routeMeters / 12)) / 4}s`" repeatCount="indefinite" rotate="auto" />
                            </g>
                        </template>
                    </g>

                    <!-- floor-change points: lift/stairs to take here, and where you come out -->
                    <g v-for="t in transitionsHere" :key="`t-${t.x}-${t.toFloor}`" :transform="`translate(${t.x} ${t.y}) scale(${k})`" data-testid="transition">
                        <g class="map-pop">
                            <rect x="-58" y="-62" width="116" height="34" rx="17" fill="#183c2d" />
                            <text y="-40" text-anchor="middle" dominant-baseline="central" fill="#fff" font-size="15" font-weight="600">
                                {{ t.kind === 'stairs' ? 'Stairs' : 'Lift' }} {{ t.direction === 'up' ? '↑' : '↓' }} {{ floorLabel(t.toFloor) }}
                            </text>
                            <path d="M-7 -28 L0 -20 L7 -28 Z" fill="#183c2d" />
                        </g>
                    </g>
                    <g v-for="t in arrivalsHere" :key="`a-${t.x}-${t.floor}`" :transform="`translate(${t.x} ${t.y}) scale(${k})`">
                        <g class="map-pop">
                            <circle r="13" fill="#fff" stroke="#183c2d" stroke-width="3" />
                            <circle r="5" fill="#183c2d" />
                        </g>
                    </g>

                    <!-- markers -->
                    <g
                        v-for="(loc, i) in locations"
                        :key="loc.id"
                        :transform="`translate(${loc.x} ${loc.y}) scale(${k})`"
                        class="map-marker"
                        :class="{ 'map-marker--dim': markerState(loc).dim, 'map-marker--inert': routeActive && !markerState(loc).inRoute }"
                        :data-loc="loc.id"
                        role="button"
                        tabindex="0"
                        :aria-label="`${loc.name}, ${CATEGORY_META[loc.category].label}`"
                        :aria-pressed="loc.id === selectedId"
                        @click="pick(loc)"
                        @keydown.enter.prevent="emit('select', loc.id)"
                        @keydown.space.prevent="emit('select', loc.id)"
                    >
                        <circle r="30" fill="transparent" />
                        <circle v-if="markerState(loc).selected || markerState(loc).dest" class="map-ring" r="22" :fill="markerState(loc).dest ? DEST_COLOR : CATEGORY_META[loc.category].color" />
                        <g class="map-marker__body" :style="{ transform: `scale(${markerState(loc).selected || markerState(loc).dest ? 1.28 : markerState(loc).highlighted ? 1.12 : 1})` }">
                            <g :class="{ 'map-float': !reduced }" :style="{ animationDelay: `${(i % 7) * -0.6}s` }">
                                <g :class="{ 'map-bounce': markerState(loc).dest }">
                                    <circle cy="2" r="17" fill="rgba(0,0,0,0.16)" />
                                    <circle class="map-marker__disc" r="17" :fill="markerState(loc).dest ? DEST_COLOR : CATEGORY_META[loc.category].color" stroke="#fff" stroke-width="3" />
                                    <text text-anchor="middle" dominant-baseline="central" font-size="16" fill="#fff">{{ CATEGORY_META[loc.category].icon }}</text>
                                </g>
                            </g>
                        </g>
                        <text v-if="showLabel(loc)" y="36" text-anchor="middle" font-size="14" font-weight="600" fill="#20241f" stroke="#fbf9f4" stroke-width="4" paint-order="stroke" class="map-label">
                            {{ loc.name }}
                        </text>
                    </g>

                    <!-- route start (when different from where the guest is) -->
                    <g v-if="originPoint" :transform="`translate(${originPoint.x} ${originPoint.y}) scale(${k})`">
                        <g class="map-pop"><circle r="10" fill="#fff" stroke="#183c2d" stroke-width="4" /></g>
                    </g>

                    <!-- destination emphasis -->
                    <g v-if="destLoc && destLoc.floor === floorId && routeActive" :transform="`translate(${destLoc.x} ${destLoc.y}) scale(${k})`" pointer-events="none">
                        <circle class="map-ring map-ring--slow" r="26" fill="none" :stroke="DEST_COLOR" stroke-width="3" />
                    </g>

                    <!-- YOU ARE HERE -->
                    <g v-if="youXY" :transform="`translate(${youXY.x} ${youXY.y}) scale(${k})`" pointer-events="none" data-testid="you-are-here">
                        <circle class="map-you-pulse" r="26" :fill="YOU_COLOR" />
                        <circle r="11" fill="#fff" />
                        <circle r="7.5" :fill="YOU_COLOR" />
                    </g>
                </g>
            </Transition>
        </svg>
    </div>
</template>
