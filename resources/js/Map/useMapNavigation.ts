import { computed, ref } from 'vue';
import { CATEGORY_META, GROUPS } from './categories';
import { buildGraph, originFromLocation, originFromPoint, planRoute } from './routing';
import type { HotelMapData, MapLocation, Origin, Point, Route } from './types';

/** The six experience states from the product spec. */
export type MapState = 'explore' | 'selected' | 'choosing' | 'preview' | 'navigating' | 'arrived';

const norm = (s: string) => s.toLowerCase().normalize('NFKD').replace(/[^\p{L}\p{N} ]/gu, '');

/**
 * All map interaction state + transitions, kept free of any DOM so it can be tested directly.
 *
 *   explore --select--> selected --directions--> preview --start--> navigating --last step--> arrived
 *                                     \--(no start known)--> choosing --origin set--/
 * "here" is the guest's own position (You Are Here). The route ORIGIN defaults to it but can be
 * changed in the From field without moving "here".
 */
export function useMapNavigation(data: HotelMapData) {
    const graph = buildGraph(data);
    const byId = new Map(data.locations.map((l) => [l.id, l]));
    const floorsSorted = [...data.floors].sort((a, b) => b.level - a.level); // top floor first (selector order)

    const startLocation = data.default_start ? byId.get(data.default_start) : undefined;
    const here = ref<Origin | null>(startLocation ? originFromLocation(data, startLocation) : null);

    const floorId = ref<string>(startLocation?.floor ?? data.floors.find((f) => f.level === 0)?.id ?? data.floors[0]?.id ?? '');
    const floorDirection = ref<'up' | 'down'>('up');
    const state = ref<MapState>('explore');
    const selectedId = ref<string | null>(null);
    const expanded = ref(false);
    const routeOrigin = ref<Origin | null>(null);
    const destinationId = ref<string | null>(null);
    const route = ref<Route | null>(null);
    const routeError = ref<string | null>(null);
    const stepIndex = ref(0);
    const pickingStart = ref(false);
    const group = ref('explore');
    const query = ref('');

    const selected = computed(() => (selectedId.value ? byId.get(selectedId.value) ?? null : null));
    const destination = computed(() => (destinationId.value ? byId.get(destinationId.value) ?? null : null));
    const currentStep = computed(() => route.value?.steps[stepIndex.value] ?? null);
    const routeActive = computed(() => ['preview', 'navigating', 'arrived'].includes(state.value) && !!route.value);

    const highlightIds = computed<Set<string> | null>(() => {
        const g = GROUPS.find((x) => x.id === group.value);
        if (!g || !g.categories) return null;
        return new Set(data.locations.filter((l) => g.categories!.includes(l.category)).map((l) => l.id));
    });

    const routeFloors = computed(() => new Set(route.value?.floors ?? []));

    function setFloor(id: string) {
        if (id === floorId.value || !data.floors.some((f) => f.id === id)) return;
        const lv = (f: string) => data.floors.find((x) => x.id === f)?.level ?? 0;
        floorDirection.value = lv(id) > lv(floorId.value) ? 'up' : 'down';
        floorId.value = id;
    }

    /* ------------------------------- selecting ------------------------------- */
    function select(id: string) {
        const loc = byId.get(id);
        if (!loc) return;
        if (pickingStart.value) return setStartFromLocation(loc);
        if (state.value !== 'explore' && state.value !== 'selected') return; // markers are inert while routing
        selectedId.value = id;
        expanded.value = false;
        state.value = 'selected';
        setFloor(loc.floor);
    }

    function deselect() {
        if (state.value !== 'selected') return;
        selectedId.value = null;
        expanded.value = false;
        state.value = 'explore';
    }

    /* ------------------------------- directions ------------------------------- */
    function computeRoute() {
        routeError.value = null;
        route.value = null;
        const dest = destination.value;
        if (!routeOrigin.value || !dest) return;
        if (routeOrigin.value.locationId === dest.id) {
            routeError.value = "You're already here.";
            return;
        }
        const r = planRoute(graph, data, routeOrigin.value, dest);
        if (!r) routeError.value = 'No walking route was found between these places.';
        route.value = r;
    }

    function requestDirections(destId?: string) {
        const id = destId ?? selectedId.value;
        if (!id || !byId.has(id)) return;
        destinationId.value = id;
        selectedId.value = id;
        routeOrigin.value = routeOrigin.value ?? here.value;
        stepIndex.value = 0;
        if (!routeOrigin.value) {
            state.value = 'choosing';
            return;
        }
        computeRoute();
        state.value = 'preview';
        setFloor(routeOrigin.value.floor);
    }

    function setOrigin(o: Origin) {
        routeOrigin.value = o;
        pickingStart.value = false;
        if (destinationId.value) {
            computeRoute();
            state.value = 'preview';
            setFloor(o.floor);
        }
    }

    function setDestination(id: string) {
        if (!byId.has(id)) return;
        destinationId.value = id;
        selectedId.value = id;
        if (routeOrigin.value) {
            computeRoute();
            state.value = 'preview';
        }
    }

    function swap() {
        const dest = destination.value;
        const from = routeOrigin.value;
        if (!dest || !from?.locationId) return; // a tapped point has no location to swap into
        const newOrigin = originFromLocation(data, dest);
        if (!newOrigin) return;
        destinationId.value = from.locationId;
        routeOrigin.value = newOrigin;
        computeRoute();
        state.value = 'preview';
        setFloor(newOrigin.floor);
    }

    function cancelRoute() {
        route.value = null;
        routeError.value = null;
        stepIndex.value = 0;
        pickingStart.value = false;
        destinationId.value = null;
        state.value = selectedId.value ? 'selected' : 'explore';
    }

    /* ------------------------------- navigating ------------------------------- */
    function startNavigation() {
        if (!route.value) return;
        stepIndex.value = 0;
        state.value = route.value.steps.length > 1 ? 'navigating' : 'arrived';
        syncFloorToStep();
    }

    function syncFloorToStep() {
        const s = currentStep.value;
        if (s) setFloor(s.floor);
    }

    function goToStep(i: number) {
        if (!route.value) return;
        const last = route.value.steps.length - 1;
        stepIndex.value = Math.max(0, Math.min(last, i));
        state.value = stepIndex.value === last ? 'arrived' : 'navigating';
        syncFloorToStep();
    }
    const nextStep = () => goToStep(stepIndex.value + 1);
    const prevStep = () => goToStep(stepIndex.value - 1);

    /** After arriving: the guest is now at the destination; show its information card. */
    function finish() {
        const dest = destination.value;
        if (dest) {
            const o = originFromLocation(data, dest);
            if (o) here.value = o;
        }
        route.value = null;
        routeOrigin.value = null;
        stepIndex.value = 0;
        selectedId.value = destinationId.value;
        destinationId.value = null;
        state.value = selectedId.value ? 'selected' : 'explore';
    }

    /* ------------------------- setting the start point ------------------------- */
    function setStartFromLocation(loc: MapLocation) {
        const o = originFromLocation(data, loc);
        if (!o) return;
        pickingStart.value = false;
        if (state.value === 'choosing' || state.value === 'preview') setOrigin(o);
        else here.value = o;
    }

    /** Tap on empty map while picking: snap to the nearest walkway point on the visible floor. */
    function tapMap(p: Point) {
        if (!pickingStart.value) return false;
        const o = originFromPoint(data, floorId.value, p);
        if (!o) return false;
        pickingStart.value = false;
        if (state.value === 'choosing' || state.value === 'preview') setOrigin(o);
        else here.value = o;
        return true;
    }

    /* --------------------------------- search --------------------------------- */
    const results = computed(() => {
        const q = norm(query.value.trim());
        if (!q) return [];
        const scored: { loc: MapLocation; score: number }[] = [];
        for (const loc of data.locations) {
            const floor = data.floors.find((f) => f.id === loc.floor);
            const name = norm(loc.name);
            const hay = `${name} ${norm(CATEGORY_META[loc.category].label)} ${norm(floor?.name ?? '')} ${norm(floor?.label ?? '')}`;
            let score = 0;
            if (name === q) score = 100;
            else if (name.startsWith(q)) score = 80;
            else if (name.split(' ').some((w) => w.startsWith(q))) score = 60;
            else if (hay.includes(q)) score = 30;
            if (score) scored.push({ loc, score: score - (loc.category === 'transport' || loc.category === 'restroom' ? 5 : 0) });
        }
        return scored.sort((a, b) => b.score - a.score || a.loc.name.localeCompare(b.loc.name)).slice(0, 12).map((s) => s.loc);
    });

    /** Shown when the search box is focused but empty. */
    const suggestions = computed(() =>
        data.locations.filter((l) => ['dining', 'cafe', 'wellness', 'fitness', 'pool', 'meeting', 'reception', 'facility'].includes(l.category)).slice(0, 8),
    );

    return {
        data, graph, byId, floorsSorted,
        state, floorId, floorDirection, selectedId, selected, expanded, here, routeOrigin, destinationId, destination,
        route, routeError, stepIndex, currentStep, routeActive, routeFloors, pickingStart, group, query, highlightIds, results, suggestions,
        setFloor, select, deselect, requestDirections, setOrigin, setDestination, swap, cancelRoute,
        startNavigation, goToStep, nextStep, prevStep, finish, setStartFromLocation, tapMap,
    };
}

export type MapNavigation = ReturnType<typeof useMapNavigation>;
