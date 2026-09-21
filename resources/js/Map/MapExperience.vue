<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import CategoryChips from './CategoryChips.vue';
import DirectionsPanel from './DirectionsPanel.vue';
import FloorSelector from './FloorSelector.vue';
import LocationSheet from './LocationSheet.vue';
import MapCanvas from './MapCanvas.vue';
import SearchPanel from './SearchPanel.vue';
import { GROUPS } from './categories';
import { useCamera } from './camera';
import { useMapNavigation } from './useMapNavigation';
import type { HotelMapData, MapFloor, Point } from './types';

const props = defineProps<{ data: HotelMapData }>();

const nav = useMapNavigation(props.data);
const camera = useCamera();
const pickingOrigin = ref(false); // choosing a route start via search
const simulating = ref(false);
let initialised = false;

const floor = computed(() => props.data.floors.find((f) => f.id === nav.floorId.value)!);
const floorName = (id: string) => props.data.floors.find((f) => f.id === id)?.name ?? '';

/* ---------------- layout: keep the map's focus in the VISIBLE part ---------------- */
function updatePadding() {
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
    const wide = window.matchMedia?.('(min-width: 768px)').matches ?? true;
    const panelOpen = nav.state.value !== 'explore' || pickingOrigin.value;
    camera.padding.value = wide
        ? { left: 29 * rem, right: 6 * rem, top: 0, bottom: 0 } // side panel on the left, floor selector on the right
        : { left: 0, right: 0, top: 9 * rem, bottom: panelOpen ? camera.height.value * 0.5 : 0 }; // bottom sheet
}

function floorBounds(f: MapFloor) {
    const b = f.areas.find((a) => a.kind === 'building');
    return b ? { x: b.x, y: b.y, w: b.w, h: b.h } : { x: 0, y: 0, w: f.width, h: f.height };
}

function boundsOf(points: Point[], pad: number) {
    const xs = points.map((p) => p.x);
    const ys = points.map((p) => p.y);
    const x = Math.min(...xs) - pad;
    const y = Math.min(...ys) - pad;
    return { x, y, w: Math.max(...xs) - Math.min(...xs) + pad * 2, h: Math.max(...ys) - Math.min(...ys) + pad * 2 };
}

watch(
    () => camera.width.value,
    (w) => {
        if (w > 0 && !initialised) {
            initialised = true;
            updatePadding();
            camera.fitHome(floorBounds(floor.value), 28, true);
            if (nav.here.value) camera.focus(nav.here.value.x, nav.here.value.y, camera.fitZoom.value * 1.5);
        }
    },
    { immediate: true },
);

/* ---------------- camera follows the experience ---------------- */
watch([() => nav.state.value, pickingOrigin], async () => {
    await nextTick();
    updatePadding();
});

// A place was chosen: glide to it (centred in the un-covered area) and zoom in a little.
watch(
    () => nav.selectedId.value,
    async (id) => {
        const loc = id ? nav.byId.get(id) : null;
        if (!loc || nav.routeActive.value) return;
        await nextTick();
        updatePadding();
        camera.focus(loc.x, loc.y, camera.fitZoom.value * 2.3);
    },
);

// Route preview: frame the whole route on the floor being shown.
watch([() => nav.state.value, () => nav.floorId.value, () => nav.route.value], async ([state]) => {
    if (state !== 'preview' || !nav.route.value) return;
    const seg = nav.route.value.segments.find((s) => s.floor === nav.floorId.value);
    await nextTick();
    updatePadding();
    if (seg) camera.fit(boundsOf(seg.points, 70), 30);
});

// Active navigation: follow the current step.
watch([() => nav.stepIndex.value, () => nav.state.value], async ([, state]) => {
    if (state !== 'navigating' && state !== 'arrived') return;
    await nextTick();
    updatePadding();
    const dest = nav.destination.value;
    const target = state === 'arrived' && dest ? { x: dest.x, y: dest.y } : nav.currentStep.value?.at;
    if (target) camera.focus(target.x, target.y, camera.fitZoom.value * 2.6);
});

// You-are-here marker: follows the step while navigating.
const you = computed(() => {
    if (nav.state.value === 'arrived' && nav.destination.value) {
        const d = nav.destination.value;
        return { floor: d.floor, x: d.x, y: d.y };
    }
    const s = nav.state.value === 'navigating' ? nav.currentStep.value : null;
    return s ? { floor: s.floor, x: s.at.x, y: s.at.y } : null;
});

// Category chips: highlight, and re-frame on the matching places.
function chooseGroup(id: string) {
    nav.group.value = id;
    if (id === 'explore') {
        camera.fitHome(floorBounds(floor.value), 28);
        return;
    }
    const ids = nav.highlightIds.value;
    if (!ids) return;
    const matches = props.data.locations.filter((l) => ids.has(l.id));
    if (!matches.length) return;
    const onFloor = matches.filter((l) => l.floor === nav.floorId.value);
    if (!onFloor.length) {
        // jump to the floor with the most matches
        const counts = new Map<string, number>();
        matches.forEach((l) => counts.set(l.floor, (counts.get(l.floor) ?? 0) + 1));
        nav.setFloor([...counts.entries()].sort((a, b) => b[1] - a[1])[0][0]);
    }
    const here = matches.filter((l) => l.floor === nav.floorId.value);
    updatePadding();
    camera.fit(boundsOf(here, 110), 40);
}

/* ---------------- interactions ---------------- */
function onSelect(id: string) {
    if (pickingOrigin.value) {
        const loc = nav.byId.get(id);
        if (loc) nav.setStartFromLocation(loc);
        pickingOrigin.value = false;
        nav.query.value = '';
        return;
    }
    nav.select(id);
}

function onMapTap(p: Point) {
    if (nav.tapMap(p)) return;
    nav.deselect();
}

function startPickOnMap() {
    pickingOrigin.value = false;
    nav.pickingStart.value = true;
}

function recenter() {
    const h = nav.here.value;
    if (h) {
        nav.setFloor(h.floor);
        camera.focus(h.x, h.y, camera.fitZoom.value * 2.2);
    } else {
        camera.fitHome(floorBounds(floor.value), 28);
    }
}

function back() {
    if (nav.pickingStart.value) return void (nav.pickingStart.value = false);
    if (pickingOrigin.value) return void (pickingOrigin.value = false);
    if (['choosing', 'preview', 'navigating', 'arrived'].includes(nav.state.value)) return nav.cancelRoute();
    nav.deselect();
}

const onKey = (e: KeyboardEvent) => e.key === 'Escape' && back();

/* Simulated walk: steps advance on their own so the guest can watch the route play out. */
let simTimer = 0;
watch(simulating, (on) => {
    window.clearInterval(simTimer);
    if (!on) return;
    simTimer = window.setInterval(() => {
        if (nav.state.value !== 'navigating') return void (simulating.value = false);
        nav.nextStep();
    }, 3800);
});
watch(() => nav.state.value, (s) => {
    if (s !== 'navigating') simulating.value = false;
});

onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    window.clearInterval(simTimer);
});

const panelKind = computed(() => {
    if (pickingOrigin.value) return 'origin';
    switch (nav.state.value) {
        case 'selected': return nav.selected.value ? 'sheet' : null;
        case 'choosing': case 'preview': case 'navigating': case 'arrived': return nav.destination.value ? 'directions' : null;
        default: return null;
    }
});
</script>

<template>
    <div
        class="relative box-border"
        style="height: calc(100dvh - var(--kiosk-dock-h, 0px)); padding-top: var(--kiosk-topbar-h, 0px); background: #e9e2d3"
        data-testid="map-experience"
    >
        <div class="relative h-full w-full overflow-hidden">
            <MapCanvas
                :data="data"
                :floor-id="nav.floorId.value"
                :floor-direction="nav.floorDirection.value"
                :camera="camera"
                :selected-id="nav.selectedId.value"
                :destination-id="nav.destinationId.value"
                :route-origin-id="nav.routeOrigin.value?.locationId ?? null"
                :here="nav.here.value"
                :you="you"
                :route="nav.route.value"
                :route-active="nav.routeActive.value"
                :step-index="nav.state.value === 'navigating' || nav.state.value === 'arrived' ? nav.stepIndex.value : null"
                :highlight-ids="nav.highlightIds.value"
                :picking-start="nav.pickingStart.value"
                @select="onSelect"
                @tap="onMapTap"
            />

            <!-- search -->
            <div v-if="!pickingOrigin" class="absolute left-3 right-3 top-3 z-40 md:left-6 md:right-auto md:top-6 md:w-[26rem]">
                <SearchPanel v-model="nav.query.value" :results="nav.results.value" :suggestions="nav.suggestions.value" :floors="data.floors" @select="onSelect" />
            </div>

            <!-- quick actions -->
            <div class="absolute left-3 right-3 top-[5.25rem] z-30 md:left-[28.5rem] md:right-24 md:top-6">
                <CategoryChips :groups="GROUPS" :active="nav.group.value" @select="chooseGroup" />
            </div>

            <!-- info card / directions: side panel on wide screens, bottom sheet on phones -->
            <div class="absolute z-30 max-md:inset-x-3 max-md:bottom-3 max-md:max-h-[55%] md:left-6 md:top-[6.25rem] md:w-[26rem] md:max-h-[calc(100%-7.5rem)] overflow-y-auto no-scrollbar">
                <Transition name="sheet">
                    <div v-if="panelKind === 'origin'" key="origin" class="glass rounded-[32px] p-5" data-testid="origin-picker">
                        <p class="mb-3 text-lg font-semibold text-slate-900">Choose your starting point</p>
                        <SearchPanel v-model="nav.query.value" :results="nav.results.value" :suggestions="nav.suggestions.value" :floors="data.floors" placeholder="Search where you are" @select="onSelect" />
                        <button type="button" class="mt-3 h-14 w-full rounded-full border border-[#183c2d]/25 text-lg font-semibold text-[#183c2d] active:scale-95" @click="startPickOnMap">Tap my location on the map</button>
                        <button type="button" class="mt-1 h-12 w-full text-base text-slate-500" @click="pickingOrigin = false">Cancel</button>
                    </div>
                    <LocationSheet
                        v-else-if="panelKind === 'sheet' && nav.selected.value"
                        :key="`sheet-${nav.selected.value.id}`"
                        :loc="nav.selected.value"
                        :floor-name="floorName(nav.selected.value.floor)"
                        :expanded="nav.expanded.value"
                        :is-here="nav.here.value?.locationId === nav.selected.value.id"
                        @close="nav.deselect()"
                        @toggle="nav.expanded.value = !nav.expanded.value"
                        @directions="nav.requestDirections()"
                        @here="nav.setStartFromLocation(nav.selected.value!)"
                    />
                    <DirectionsPanel
                        v-else-if="panelKind === 'directions' && nav.destination.value"
                        key="directions"
                        :mode="nav.state.value as 'choosing' | 'preview' | 'navigating' | 'arrived'"
                        :route="nav.route.value"
                        :origin="nav.routeOrigin.value"
                        :destination="nav.destination.value"
                        :step-index="nav.stepIndex.value"
                        :error="nav.routeError.value"
                        :floors="data.floors"
                        :simulating="simulating"
                        @start="nav.startNavigation()"
                        @cancel="nav.cancelRoute()"
                        @prev="nav.prevStep()"
                        @next="nav.nextStep()"
                        @goto="(i: number) => nav.goToStep(i)"
                        @swap="nav.swap()"
                        @change-origin="pickingOrigin = true"
                        @pick-on-map="startPickOnMap"
                        @finish="nav.finish()"
                        @simulate="simulating = !simulating"
                    />
                </Transition>
            </div>

            <!-- floors -->
            <div class="absolute right-3 top-1/2 z-30 -translate-y-1/2 md:right-6">
                <FloorSelector :floors="nav.floorsSorted" :current="nav.floorId.value" :route-floors="nav.routeFloors.value" @select="nav.setFloor" />
            </div>

            <!-- zoom / recentre / set start -->
            <div class="absolute bottom-6 right-6 z-30 flex flex-col gap-3 max-md:hidden">
                <button type="button" class="glass h-14 w-14 rounded-full text-2xl text-[#183c2d] active:scale-90" aria-label="Set my starting point" title="Set my starting point" data-testid="set-start" :class="{ 'ring-4 ring-[#2b6fd6]/40': nav.pickingStart.value }" @click="nav.pickingStart.value = !nav.pickingStart.value">📍</button>
                <button type="button" class="glass h-14 w-14 rounded-full text-xl text-[#183c2d] active:scale-90" aria-label="Centre on my location" data-testid="recenter" @click="recenter">◎</button>
                <div class="glass overflow-hidden rounded-full">
                    <button type="button" class="block h-14 w-14 text-3xl text-[#183c2d] active:bg-black/10" aria-label="Zoom in" data-testid="zoom-in" @click="camera.zoomBy(1.45)">+</button>
                    <button type="button" class="block h-14 w-14 border-t border-black/5 text-3xl text-[#183c2d] active:bg-black/10" aria-label="Zoom out" data-testid="zoom-out" @click="camera.zoomBy(1 / 1.45)">−</button>
                </div>
            </div>

            <!-- "tap your location" hint -->
            <Transition name="hint">
                <div v-if="nav.pickingStart.value" class="glass absolute left-1/2 top-24 z-40 flex -translate-x-1/2 items-center gap-4 rounded-full py-2 pl-7 pr-2 text-lg font-medium text-slate-900 max-md:top-40" data-testid="pick-hint">
                    Tap where you are on the map
                    <button type="button" class="h-11 rounded-full bg-black/5 px-5 text-base active:scale-95" @click="nav.pickingStart.value = false">Cancel</button>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.hint-enter-active { transition: opacity 300ms var(--ease-standard), transform 500ms var(--ease-spring); }
.hint-leave-active { transition: opacity 150ms var(--ease-standard); }
.hint-enter-from { opacity: 0; transform: translate(-50%, -14px) scale(0.96); }
.hint-leave-to { opacity: 0; }
</style>
