import { computed, ref } from 'vue';
import { buildGraph, nearestNode, nodeForLocation, originFromLocation, planRoute } from '../routing';
import type { AreaKind, HotelMapData, LocationCategory, MapArea, MapFloor, MapLocation, MapNode, NodeType, Point, Route } from '../types';

export type Tool = 'select' | 'area' | 'location' | 'path';
export type Selection = { kind: 'area' | 'node' | 'location'; id: string } | null;
export type Side = 'n' | 's' | 'e' | 'w';

export interface Issue {
    level: 'error' | 'warning';
    text: string;
    /** Click to jump there. */
    target?: { kind: 'area' | 'node' | 'location'; id: string; floor: string };
}

const SNAP = 10;
const DOOR_OFFSET = 50;
const MIN_AREA = 20;
const HISTORY_LIMIT = 100;

const clone = <T>(v: T): T => JSON.parse(JSON.stringify(v));
const dist = (a: Point, b: Point) => Math.hypot(a.x - b.x, a.y - b.y);
export const slugify = (s: string) => s.toLowerCase().normalize('NFKD').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'item';

export function blankMap(): HotelMapData {
    return {
        version: 1,
        meters_per_unit: 0.1,
        default_start: null,
        floors: [{ id: 'fg', label: 'G', name: 'Ground Floor', level: 0, width: 1000, height: 600, plan_image: null, areas: [] }],
        nodes: [],
        locations: [],
    };
}

/**
 * Everything the visual map editor can do, with no DOM in it (so it can be tested directly).
 * Every edit is one undo step; drags are one step per drag (beginGesture/endGesture).
 * The output is the SAME document the guest map and the server validator use.
 */
export function useMapBuilder(initial: HotelMapData | null) {
    const data = ref<HotelMapData>(initial && initial.floors?.length ? clone(initial) : blankMap());

    const floorId = ref<string>([...data.value.floors].sort((a, b) => a.level - b.level).find((f) => f.level === 0)?.id ?? data.value.floors[0].id);
    const tool = ref<Tool>('select');
    const areaKind = ref<AreaKind>('room');
    const placeCategory = ref<LocationCategory>('room');
    const pathNodeType = ref<NodeType>('walk');
    const selection = ref<Selection>(null);
    const pathFrom = ref<string | null>(null);
    /** While set, the next walkway point clicked becomes this place's door. */
    const doorPick = ref<string | null>(null);
    const snapOn = ref(true);
    const ortho = ref(true);
    const dirty = ref(false);

    /* ----------------------------- history ----------------------------- */
    const past: string[] = [];
    const future: string[] = [];
    const hist = ref(0);
    let inGesture = false;
    /** Locations whose door the user chose by hand: moving them no longer re-picks the nearest walkway. */
    const pinnedDoors = new Set<string>();

    function checkpoint() {
        past.push(JSON.stringify(data.value));
        if (past.length > HISTORY_LIMIT) past.shift();
        future.length = 0;
        dirty.value = true;
        hist.value++;
    }
    function beginGesture() {
        if (!inGesture) {
            checkpoint();
            inGesture = true;
        }
    }
    const endGesture = () => void (inGesture = false);

    function restore(json: string) {
        data.value = JSON.parse(json);
        if (!data.value.floors.some((f) => f.id === floorId.value)) floorId.value = data.value.floors[0]?.id ?? '';
        if (selection.value && !exists(selection.value)) selection.value = null;
        if (pathFrom.value && !data.value.nodes.some((n) => n.id === pathFrom.value)) pathFrom.value = null;
        dirty.value = true;
        hist.value++;
    }
    function undo() {
        const prev = past.pop();
        if (prev === undefined) return;
        future.push(JSON.stringify(data.value));
        restore(prev);
    }
    function redo() {
        const next = future.pop();
        if (next === undefined) return;
        past.push(JSON.stringify(data.value));
        restore(next);
    }
    const canUndo = computed(() => (hist.value, past.length > 0));
    const canRedo = computed(() => (hist.value, future.length > 0));

    function exists(s: NonNullable<Selection>) {
        const d = data.value;
        return s.kind === 'area' ? d.floors.some((f) => f.areas.some((a) => a.id === s.id)) : s.kind === 'node' ? d.nodes.some((n) => n.id === s.id) : d.locations.some((l) => l.id === s.id);
    }

    /* ----------------------------- lookups ----------------------------- */
    const floors = computed(() => [...data.value.floors].sort((a, b) => a.level - b.level));
    const floor = computed(() => data.value.floors.find((f) => f.id === floorId.value)!);
    const nodesHere = computed(() => data.value.nodes.filter((n) => n.floor === floorId.value));
    const locationsHere = computed(() => data.value.locations.filter((l) => l.floor === floorId.value));
    const nodeMap = computed(() => new Map(data.value.nodes.map((n) => [n.id, n])));
    const node = (id: string) => data.value.nodes.find((n) => n.id === id);
    const location = (id: string) => data.value.locations.find((l) => l.id === id);
    const area = (id: string) => data.value.floors.flatMap((f) => f.areas).find((a) => a.id === id);
    const floorOf = (id: string) => data.value.floors.find((f) => f.id === id);

    const selectedArea = computed(() => (selection.value?.kind === 'area' ? area(selection.value.id) ?? null : null));
    const selectedNode = computed(() => (selection.value?.kind === 'node' ? node(selection.value.id) ?? null : null));
    const selectedLocation = computed(() => (selection.value?.kind === 'location' ? location(selection.value.id) ?? null : null));

    function uid(prefix: string, taken: Iterable<string>) {
        const set = new Set(taken);
        let id: string;
        do id = `${prefix}-${Math.random().toString(36).slice(2, 7)}`;
        while (set.has(id));
        return id;
    }
    const clampX = (x: number, f = floor.value) => Math.min(f.width, Math.max(0, Math.round(x)));
    const clampY = (y: number, f = floor.value) => Math.min(f.height, Math.max(0, Math.round(y)));

    /** Grid snap, plus magnetic snap to an existing walkway point (so paths join up exactly). */
    function snapPoint(p: Point, opts: { radius?: number; exclude?: string; nodes?: boolean } = {}): Point {
        if (opts.nodes && opts.radius) {
            let best: MapNode | null = null;
            let bestD = opts.radius;
            for (const n of nodesHere.value) {
                if (n.id === opts.exclude) continue;
                const d = dist(n, p);
                if (d < bestD) {
                    best = n;
                    bestD = d;
                }
            }
            if (best) return { x: best.x, y: best.y };
        }
        const g = snapOn.value ? SNAP : 1;
        return { x: clampX(Math.round(p.x / g) * g), y: clampY(Math.round(p.y / g) * g) };
    }

    /** With "straight lines" on, a new point lines up horizontally or vertically with the previous one. */
    function orthoFrom(fromId: string | null, p: Point): Point {
        const from = fromId ? node(fromId) : null;
        if (!from || !ortho.value) return p;
        return Math.abs(p.x - from.x) >= Math.abs(p.y - from.y) ? { x: p.x, y: from.y } : { x: from.x, y: p.y };
    }

    /* ------------------------------ floors ------------------------------ */
    function addFloor(opts: { label: string; name: string; level: number; copyFrom?: string | null }) {
        checkpoint();
        const id = uid('f' + slugify(opts.label), data.value.floors.map((f) => f.id));
        const nf: MapFloor = { id, label: opts.label, name: opts.name, level: opts.level, width: 1000, height: 600, plan_image: null, areas: [] };
        const src = opts.copyFrom ? floorOf(opts.copyFrom) : null;
        if (src) {
            nf.width = src.width;
            nf.height = src.height;
            nf.plan_image = src.plan_image ?? null;
            nf.areas = src.areas.map((a) => ({ ...a, id: uid(id + '-a', []) }));
            const srcNodes = data.value.nodes.filter((n) => n.floor === src.id);
            const remap = new Map<string, string>();
            const taken = data.value.nodes.map((n) => n.id);
            for (const n of srcNodes) {
                const nid = uid('n', taken);
                taken.push(nid);
                remap.set(n.id, nid);
            }
            for (const n of srcNodes) {
                const copy: MapNode = { ...clone(n), id: remap.get(n.id)!, floor: id, connections: n.connections.filter((c) => remap.has(c)).map((c) => remap.get(c)!) };
                // lift/stairs line up with their twin on the floor they were copied from
                if (n.type === 'elevator' || n.type === 'stairs') copy.connections.push(n.id);
                data.value.nodes.push(copy);
                if (copy.connections.includes(n.id)) n.connections.push(copy.id);
            }
        }
        data.value.floors.push(nf);
        floorId.value = id;
        selection.value = null;
        return id;
    }

    function updateFloor(id: string, patch: Partial<Pick<MapFloor, 'label' | 'name' | 'level' | 'plan_image'>>) {
        const f = floorOf(id);
        if (!f) return;
        checkpoint();
        Object.assign(f, patch);
        if (f.plan_image === '') f.plan_image = null;
    }

    function deleteFloor(id: string) {
        if (data.value.floors.length <= 1) return false;
        checkpoint();
        const gone = new Set(data.value.nodes.filter((n) => n.floor === id).map((n) => n.id));
        data.value.floors = data.value.floors.filter((f) => f.id !== id);
        data.value.nodes = data.value.nodes.filter((n) => n.floor !== id);
        data.value.nodes.forEach((n) => (n.connections = n.connections.filter((c) => !gone.has(c))));
        const lost = data.value.locations.filter((l) => l.floor === id).map((l) => l.id);
        data.value.locations = data.value.locations.filter((l) => l.floor !== id);
        if (data.value.default_start && lost.includes(data.value.default_start)) data.value.default_start = null;
        if (floorId.value === id) floorId.value = floors.value[0].id;
        selection.value = null;
        pathFrom.value = null;
        return true;
    }

    /* ------------------------------- areas ------------------------------- */
    function addArea(x: number, y: number, w: number, h: number, kind: AreaKind = areaKind.value) {
        if (w < MIN_AREA || h < MIN_AREA) return null;
        checkpoint();
        const f = floor.value;
        const a: MapArea = { id: uid(f.id + '-a', f.areas.map((z) => z.id)), kind, x: clampX(x), y: clampY(y), w: Math.round(w), h: Math.round(h) };
        f.areas.push(a);
        selection.value = { kind: 'area', id: a.id };
        return a.id;
    }
    function moveArea(id: string, x: number, y: number) {
        const a = area(id);
        if (!a) return;
        a.x = Math.min(floor.value.width - a.w, Math.max(0, Math.round(x)));
        a.y = Math.min(floor.value.height - a.h, Math.max(0, Math.round(y)));
    }
    function resizeArea(id: string, x: number, y: number, w: number, h: number) {
        const a = area(id);
        if (!a) return;
        const nx = clampX(x);
        const ny = clampY(y);
        a.x = nx;
        a.y = ny;
        a.w = Math.max(MIN_AREA, Math.min(Math.round(w), floor.value.width - nx));
        a.h = Math.max(MIN_AREA, Math.min(Math.round(h), floor.value.height - ny));
    }
    function updateArea(id: string, patch: Partial<Pick<MapArea, 'kind' | 'label' | 'x' | 'y' | 'w' | 'h'>>) {
        const a = area(id);
        if (!a) return;
        checkpoint();
        Object.assign(a, patch);
        if (!a.label) delete a.label;
        resizeArea(id, a.x, a.y, a.w, a.h);
    }

    /* -------------------------------- nodes -------------------------------- */
    const doorFor = (n: { x: number; y: number }, side: Side) => ({ x: n.x + (side === 'e' ? DOOR_OFFSET : side === 'w' ? -DOOR_OFFSET : 0), y: n.y + (side === 's' ? DOOR_OFFSET : side === 'n' ? -DOOR_OFFSET : 0) });
    function liftSide(n: MapNode): Side | null {
        if (!n.door) return null;
        const dx = n.door.x - n.x;
        const dy = n.door.y - n.y;
        return Math.abs(dx) > Math.abs(dy) ? (dx > 0 ? 'e' : 'w') : dy > 0 ? 's' : 'n';
    }

    function addNode(x: number, y: number, type: NodeType = pathNodeType.value, connectTo: string | null = null) {
        checkpoint();
        const n: MapNode = { id: uid('n', data.value.nodes.map((z) => z.id)), floor: floorId.value, x: clampX(x), y: clampY(y), type, connections: [] };
        if (type === 'elevator' || type === 'stairs') n.door = doorFor(n, 'n');
        data.value.nodes.push(n);
        if (connectTo) link(connectTo, n.id);
        return n.id;
    }
    function link(a: string, b: string) {
        const na = node(a);
        const nb = node(b);
        if (!na || !nb || a === b) return false;
        if (!na.connections.includes(b)) na.connections.push(b);
        if (!nb.connections.includes(a)) nb.connections.push(a);
        return true;
    }
    function connect(a: string, b: string) {
        const na = node(a);
        const nb = node(b);
        if (!na || !nb || a === b || na.floor !== nb.floor || na.connections.includes(b)) return false;
        checkpoint();
        return link(a, b);
    }
    function disconnect(a: string, b: string) {
        checkpoint();
        const na = node(a);
        const nb = node(b);
        if (na) na.connections = na.connections.filter((c) => c !== b);
        if (nb) nb.connections = nb.connections.filter((c) => c !== a);
    }
    function moveNode(id: string, x: number, y: number) {
        const n = node(id);
        if (!n) return;
        const nx = clampX(x, floorOf(n.floor));
        const ny = clampY(y, floorOf(n.floor));
        if (n.door) n.door = { x: n.door.x + (nx - n.x), y: n.door.y + (ny - n.y) };
        n.x = nx;
        n.y = ny;
    }
    function setNodeType(id: string, type: NodeType) {
        const n = node(id);
        if (!n || n.type === type) return;
        checkpoint();
        // Only lift-to-lift / stairs-to-stairs links may cross floors, so changing the type cuts them.
        for (const c of [...n.connections]) {
            const o = node(c);
            if (o && o.floor !== n.floor) {
                n.connections = n.connections.filter((x) => x !== c);
                o.connections = o.connections.filter((x) => x !== id);
            }
        }
        n.type = type;
        if (type === 'elevator' || type === 'stairs') n.door = n.door ?? doorFor(n, 'n');
        else delete n.door;
    }
    function setLiftSide(id: string, side: Side) {
        const n = node(id);
        if (!n) return;
        checkpoint();
        n.door = doorFor(n, side);
    }

    /** All nodes joined to `id` by lift/stairs links (its "shaft"). */
    function shaftOf(id: string): MapNode[] {
        const seen = new Set([id]);
        const queue = [id];
        while (queue.length) {
            const cur = node(queue.shift()!)!;
            for (const c of cur.connections) {
                const o = node(c);
                if (o && o.floor !== cur.floor && !seen.has(c)) {
                    seen.add(c);
                    queue.push(c);
                }
            }
        }
        return [...seen].map((s) => node(s)!);
    }

    /**
     * Connect a lift / stairs to the matching one on every other floor, creating it (at the same
     * spot, joined to the nearest walkway) where a floor doesn't have one yet.
     */
    function linkShaft(id: string) {
        const n = node(id);
        if (!n || (n.type !== 'elevator' && n.type !== 'stairs')) return 0;
        checkpoint();
        const perFloor = new Map<string, MapNode>();
        for (const f of data.value.floors) {
            if (f.id === n.floor) {
                perFloor.set(f.id, n);
                continue;
            }
            let twin = shaftOf(id).find((s) => s.floor === f.id);
            if (!twin) {
                let best: MapNode | undefined;
                let bestD = Infinity;
                for (const c of data.value.nodes) {
                    if (c.floor !== f.id || c.type !== n.type || shaftOf(c.id).some((s) => s.floor === n.floor)) continue;
                    const d = dist(c, n);
                    if (d < bestD) {
                        best = c;
                        bestD = d;
                    }
                }
                twin = best;
            }
            if (!twin) {
                const made: MapNode = { id: uid('n', data.value.nodes.map((z) => z.id)), floor: f.id, x: Math.min(n.x, f.width), y: Math.min(n.y, f.height), type: n.type, connections: [], door: n.door ? { ...n.door } : undefined };
                if (!made.door) delete made.door;
                data.value.nodes.push(made);
                const near = nearestNode(data.value, f.id, made);
                if (near && near.id !== made.id && near.type === 'walk') link(made.id, near.id);
                twin = made;
            }
            perFloor.set(f.id, twin);
        }
        const ordered = floors.value.map((f) => perFloor.get(f.id)!).filter(Boolean);
        let joined = 0;
        for (let i = 0; i + 1 < ordered.length; i++) if (link(ordered[i].id, ordered[i + 1].id)) joined++;
        return joined;
    }
    function unlinkShaft(id: string) {
        checkpoint();
        for (const s of shaftOf(id)) {
            for (const c of [...s.connections]) {
                const o = node(c);
                if (o && o.floor !== s.floor) {
                    s.connections = s.connections.filter((x) => x !== c);
                    o.connections = o.connections.filter((x) => x !== s.id);
                }
            }
        }
    }

    /* ------------------------------ locations ------------------------------ */
    function addLocation(x: number, y: number, category: LocationCategory = placeCategory.value) {
        checkpoint();
        const f = floor.value;
        const base = category === 'room' ? 'room' : slugify(category);
        const id = uid(base, data.value.locations.map((l) => l.id));
        const n = nearestNode(data.value, f.id, { x, y });
        const l: MapLocation = { id, name: 'New place', description: null, image: null, floor: f.id, x: clampX(x), y: clampY(y), category, opening_hours: null, node: n?.id ?? null };
        data.value.locations.push(l);
        selection.value = { kind: 'location', id };
        return id;
    }
    function moveLocation(id: string, x: number, y: number) {
        const l = location(id);
        if (!l) return;
        const f = floorOf(l.floor);
        l.x = clampX(x, f);
        l.y = clampY(y, f);
        if (!pinnedDoors.has(id)) l.node = nearestNode(data.value, l.floor, l)?.id ?? null;
    }
    function updateLocation(id: string, patch: Partial<Pick<MapLocation, 'name' | 'description' | 'image' | 'category' | 'opening_hours' | 'ref' | 'node' | 'link'>>) {
        const l = location(id);
        if (!l) return;
        checkpoint();
        Object.assign(l, patch);
        for (const k of ['description', 'image', 'opening_hours', 'link'] as const) if (l[k] === '') l[k] = null;
        if (typeof l.link === 'string') l.link = l.link.trim() || null;
        if (l.link === null) delete l.link;
        if (patch.node) pinnedDoors.add(id);
        if (!l.ref) l.ref = null;
    }
    /** Use a walkway point as a place's door (must be on the same floor). */
    function setDoor(locationId: string, nodeId: string) {
        const l = location(locationId);
        const n = node(nodeId);
        doorPick.value = null;
        if (!l || !n || n.floor !== l.floor) return false;
        updateLocation(locationId, { node: nodeId });
        return true;
    }
    function setDefaultStart(id: string | null) {
        checkpoint();
        data.value.default_start = id;
    }

    /* ------------------------------- removing ------------------------------- */
    function remove(sel: Selection = selection.value) {
        if (!sel) return;
        checkpoint();
        const d = data.value;
        if (sel.kind === 'area') {
            for (const f of d.floors) f.areas = f.areas.filter((a) => a.id !== sel.id);
        } else if (sel.kind === 'location') {
            d.locations = d.locations.filter((l) => l.id !== sel.id);
            if (d.default_start === sel.id) d.default_start = null;
        } else {
            const gone = d.nodes.find((n) => n.id === sel.id);
            d.nodes = d.nodes.filter((n) => n.id !== sel.id);
            d.nodes.forEach((n) => (n.connections = n.connections.filter((c) => c !== sel.id)));
            for (const l of d.locations) {
                if (l.node === sel.id) {
                    l.node = gone ? nearestNode(d, l.floor, l)?.id ?? null : null;
                }
            }
            if (pathFrom.value === sel.id) pathFrom.value = null;
        }
        selection.value = null;
    }

    /* -------------------------------- checks -------------------------------- */
    const issues = computed<Issue[]>(() => {
        const d = data.value;
        const out: Issue[] = [];
        const fname = (id: string) => floorOf(id)?.name ?? id;

        for (const l of d.locations) {
            if (!l.name.trim()) out.push({ level: 'error', text: `A place on the ${fname(l.floor)} has no name.`, target: { kind: 'location', id: l.id, floor: l.floor } });
            if (!nodeForLocation(d, l)) out.push({ level: 'error', text: `${l.name || 'A place'} is on the ${fname(l.floor)}, which has no walkway yet. Draw a walkway there.`, target: { kind: 'location', id: l.id, floor: l.floor } });
        }
        if (d.locations.length && !d.default_start) out.push({ level: 'warning', text: 'Choose where the kiosk is ("You are here"): select a place and tick "This is where the kiosk is".' });

        const graph = buildGraph(d);
        const start = d.default_start ? location(d.default_start) : undefined;
        const rootNode = start ? nodeForLocation(d, start)?.id : d.nodes[0]?.id;
        if (rootNode) {
            const seen = new Set([rootNode]);
            const queue = [rootNode];
            while (queue.length) for (const e of graph.adj.get(queue.shift()!) ?? []) if (!seen.has(e.to)) (seen.add(e.to), queue.push(e.to));
            for (const l of d.locations) {
                const n = nodeForLocation(d, l);
                if (n && !seen.has(n.id)) out.push({ level: 'warning', text: `No walking route to ${l.name} (its walkway isn't connected to the rest).`, target: { kind: 'location', id: l.id, floor: l.floor } });
            }
            const orphans = d.nodes.filter((n) => !seen.has(n.id) && !d.locations.some((l) => l.node === n.id));
            if (orphans.length) out.push({ level: 'warning', text: `${orphans.length} walkway point(s) aren't connected to the rest.`, target: { kind: 'node', id: orphans[0].id, floor: orphans[0].floor } });
        }
        if (d.floors.length > 1) {
            for (const n of d.nodes) {
                if ((n.type === 'elevator' || n.type === 'stairs') && !n.connections.some((c) => node(c)?.floor !== n.floor)) {
                    out.push({ level: 'warning', text: `A ${n.type === 'stairs' ? 'staircase' : 'lift'} on the ${fname(n.floor)} isn't connected to other floors.`, target: { kind: 'node', id: n.id, floor: n.floor } });
                }
            }
        }
        return out;
    });

    /* --------------------------------- test route --------------------------------- */
    function testRoute(fromId: string, toId: string): Route | null {
        const from = location(fromId);
        const to = location(toId);
        if (!from || !to || from.id === to.id) return null;
        const o = originFromLocation(data.value, from);
        return o ? planRoute(buildGraph(data.value), data.value, o, to) : null;
    }

    /* --------------------------------- output --------------------------------- */
    /** The document to save. Every place gets an explicit door node. */
    function toJSON(): HotelMapData {
        const out = clone(data.value);
        for (const l of out.locations) {
            if (!l.node || !out.nodes.some((n) => n.id === l.node)) l.node = nearestNode(out, l.floor, l)?.id ?? null;
            if (!l.node) delete l.node;
        }
        return out;
    }
    const markSaved = () => void (dirty.value = false);

    return {
        data, floorId, tool, areaKind, placeCategory, pathNodeType, selection, pathFrom, doorPick, snapOn, ortho, dirty, pinnedDoors,
        floors, floor, nodesHere, locationsHere, nodeMap, selectedArea, selectedNode, selectedLocation,
        node, location, area, floorOf, snapPoint, orthoFrom,
        canUndo, canRedo, undo, redo, beginGesture, endGesture, checkpoint,
        addFloor, updateFloor, deleteFloor,
        addArea, moveArea, resizeArea, updateArea,
        addNode, connect, disconnect, moveNode, setNodeType, setLiftSide, liftSide, shaftOf, linkShaft, unlinkShaft,
        addLocation, moveLocation, updateLocation, setDoor, setDefaultStart, remove,
        issues, testRoute, toJSON, markSaved,
    };
}

export type Builder = ReturnType<typeof useMapBuilder>;
