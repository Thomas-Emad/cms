import type { HotelMapData, MapLocation, MapNode, Origin, Point, Route, RouteSegment, Step, StepKind, Transition } from './types';

/* Tunables. "cost" is what Dijkstra minimises; distances/times shown to guests are separate. */
export const WALK_SPEED_MPS = 1.25;
const ELEVATOR_COST_PER_FLOOR = 14; // effort-metres: preferred over stairs (accessible + comfortable)
const STAIRS_COST_PER_FLOOR = 28;
const ELEVATOR_SECONDS = 40; // wait + ride, first floor
const ELEVATOR_SECONDS_EXTRA = 8;
const STAIRS_SECONDS_PER_FLOOR = 20;
const TURN_MIN_DEG = 35; // below this a bend is "straight"
const TURN_SLIGHT_MAX_DEG = 70;
const TURN_UTURN_MIN_DEG = 150;
const LANDMARK_RADIUS_UNITS = 100;

interface Edge {
    to: string;
    cost: number;
    meters: number;
    seconds: number;
    kind: 'walk' | 'elevator' | 'stairs';
}

export interface Graph {
    nodes: Map<string, MapNode>;
    adj: Map<string, Edge[]>;
    level: Map<string, number>;
    mpu: number;
}

const dist = (a: Point, b: Point) => Math.hypot(a.x - b.x, a.y - b.y);

/** Build a routing graph. Connections are treated as two-way even if only listed on one node. */
export function buildGraph(data: HotelMapData): Graph {
    const nodes = new Map(data.nodes.map((n) => [n.id, n]));
    const level = new Map(data.floors.map((f) => [f.id, f.level]));
    const adj = new Map<string, Edge[]>();
    const seen = new Set<string>();
    for (const n of data.nodes) adj.set(n.id, []);

    const link = (a: MapNode, b: MapNode) => {
        const key = a.id < b.id ? `${a.id}|${b.id}` : `${b.id}|${a.id}`;
        if (seen.has(key)) return;
        seen.add(key);

        let forward: Edge;
        if (a.floor !== b.floor) {
            const floorsApart = Math.abs((level.get(a.floor) ?? 0) - (level.get(b.floor) ?? 0)) || 1;
            const stairs = a.type === 'stairs' && b.type === 'stairs';
            forward = {
                to: b.id,
                kind: stairs ? 'stairs' : 'elevator',
                cost: floorsApart * (stairs ? STAIRS_COST_PER_FLOOR : ELEVATOR_COST_PER_FLOOR),
                meters: 0,
                seconds: stairs ? floorsApart * STAIRS_SECONDS_PER_FLOOR : ELEVATOR_SECONDS + (floorsApart - 1) * ELEVATOR_SECONDS_EXTRA,
            };
        } else {
            const meters = dist(a, b) * data.meters_per_unit;
            forward = { to: b.id, kind: 'walk', cost: meters, meters, seconds: meters / WALK_SPEED_MPS };
        }
        adj.get(a.id)!.push(forward);
        adj.get(b.id)!.push({ ...forward, to: a.id });
    };

    for (const n of data.nodes) {
        for (const id of n.connections) {
            const other = nodes.get(id);
            if (other) link(n, other);
        }
    }
    return { nodes, adj, level, mpu: data.meters_per_unit };
}

/** Shortest path by cost (Dijkstra; graphs here are tiny, so a linear scan is plenty). */
export function shortestPath(graph: Graph, from: string, to: string): string[] | null {
    if (!graph.nodes.has(from) || !graph.nodes.has(to)) return null;
    const best = new Map<string, number>([[from, 0]]);
    const prev = new Map<string, string>();
    const open = new Set<string>([from]);
    const done = new Set<string>();

    while (open.size) {
        let cur = '';
        let curCost = Infinity;
        for (const id of open) {
            const c = best.get(id)!;
            if (c < curCost) {
                curCost = c;
                cur = id;
            }
        }
        if (cur === to) break;
        open.delete(cur);
        done.add(cur);
        for (const e of graph.adj.get(cur) ?? []) {
            if (done.has(e.to)) continue;
            const next = curCost + e.cost;
            if (next < (best.get(e.to) ?? Infinity)) {
                best.set(e.to, next);
                prev.set(e.to, cur);
                open.add(e.to);
            }
        }
    }

    if (!best.has(to)) return null;
    const path = [to];
    while (path[0] !== from) path.unshift(prev.get(path[0])!);
    return path;
}

export function nearestNode(data: HotelMapData, floor: string, p: Point): MapNode | null {
    let best: MapNode | null = null;
    let bestD = Infinity;
    for (const n of data.nodes) {
        if (n.floor !== floor) continue;
        const d = dist(n, p);
        if (d < bestD) {
            bestD = d;
            best = n;
        }
    }
    return best;
}

/** The walkway node a location is reached from. */
export function nodeForLocation(data: HotelMapData, loc: MapLocation): MapNode | null {
    if (loc.node) {
        const explicit = data.nodes.find((n) => n.id === loc.node);
        if (explicit) return explicit;
    }
    return nearestNode(data, loc.floor, loc);
}

/** Turn a location or a tapped point into a route origin (snapped to the walkway). */
export function originFromLocation(data: HotelMapData, loc: MapLocation): Origin | null {
    const node = nodeForLocation(data, loc);
    return node ? { floor: loc.floor, x: loc.x, y: loc.y, nodeId: node.id, label: loc.name, locationId: loc.id } : null;
}

export function originFromPoint(data: HotelMapData, floor: string, p: Point, label = 'Your location'): Origin | null {
    const node = nearestNode(data, floor, p);
    return node ? { floor, x: node.x, y: node.y, nodeId: node.id, label } : null;
}

/* ------------------------------- instructions ------------------------------- */

/** Signed turn angle in degrees between two headings. Screen coords (y down): positive = right/clockwise. */
export function turnAngle(a: Point, b: Point): number {
    const cross = a.x * b.y - a.y * b.x;
    const dot = a.x * b.x + a.y * b.y;
    return (Math.atan2(cross, dot) * 180) / Math.PI;
}

const vec = (from: Point, to: Point): Point => ({ x: to.x - from.x, y: to.y - from.y });
const len = (v: Point) => Math.hypot(v.x, v.y);

function turnKind(angle: number): StepKind | null {
    const a = Math.abs(angle);
    if (a < TURN_MIN_DEG) return null;
    if (a >= TURN_UTURN_MIN_DEG) return 'uturn';
    const right = angle > 0;
    if (a < TURN_SLIGHT_MAX_DEG) return right ? 'slight-right' : 'slight-left';
    return right ? 'right' : 'left';
}

const TURN_TEXT: Record<string, string> = {
    left: 'Turn left',
    right: 'Turn right',
    'slight-left': 'Bear left',
    'slight-right': 'Bear right',
    uturn: 'Turn around',
    straight: 'Head straight',
};

type Action =
    | { i: number; type: 'start'; angle: number | null }
    | { i: number; type: 'turn'; kind: StepKind }
    | { i: number; type: 'vertical'; kind: 'elevator' | 'stairs'; to: number }
    | { i: number; type: 'exit'; via: 'elevator' | 'stairs'; kind: StepKind | null }
    | { i: number; type: 'arrive' };

export function planRoute(graph: Graph, data: HotelMapData, origin: Origin, dest: MapLocation): Route | null {
    const destNode = nodeForLocation(data, dest);
    if (!destNode) return null;
    const path = shortestPath(graph, origin.nodeId, destNode.id);
    if (!path) return null;

    const pts = path.map((id) => graph.nodes.get(id)!);
    const floorName = (id: string) => data.floors.find((f) => f.id === id)?.name ?? id;
    const edgeBetween = (a: string, b: string) => graph.adj.get(a)!.find((e) => e.to === b)!;
    const edges = pts.slice(1).map((n, k) => edgeBetween(pts[k].id, n.id));
    const last = pts.length - 1;

    /* ---- pass 1: where do things happen? ---- */
    const actions: Action[] = [];

    // Leaving a room: go straight out to the walkway (an "elbow": vertical first), not diagonally.
    const elbowStart = Math.abs(origin.x - pts[0].x) > 8 && Math.abs(origin.y - pts[0].y) > 20;
    const leaving: Point = elbowStart ? { x: 0, y: pts[0].y - origin.y } : vec(origin, pts[0]);
    const firstOut = last > 0 && edges[0].kind === 'walk' ? vec(pts[0], pts[1]) : null;
    actions.push({
        i: 0,
        type: 'start',
        angle: len(leaving) > 20 && firstOut ? turnAngle(leaving, firstOut) : null,
    });

    let i = 0;
    while (i < last) {
        const eOut = edges[i];
        if (eOut.kind !== 'walk') {
            // A vertical run may span several floors (elevator A->B->C): collapse it.
            let j = i;
            while (j < last && edges[j].kind === eOut.kind) j++;
            actions.push({ i, type: 'vertical', kind: eOut.kind, to: j });
            if (j < last) {
                const node = pts[j];
                const out = vec(node, pts[j + 1]);
                const exitHeading = node.door ? vec(node.door, node) : null;
                actions.push({ i: j, type: 'exit', via: eOut.kind, kind: exitHeading ? turnKind(turnAngle(exitHeading, out)) : null });
            }
            i = j + 1;
            continue;
        }
        if (i > 0 && edges[i - 1].kind === 'walk') {
            const kind = turnKind(turnAngle(vec(pts[i - 1], pts[i]), vec(pts[i], pts[i + 1])));
            if (kind) actions.push({ i, type: 'turn', kind });
        }
        i++;
    }
    // Starting right on a lift/stairs: the ride is the first instruction, no "head straight" before it.
    if (actions.some((a) => a.type === 'vertical' && a.i === 0)) actions.shift();
    actions.push({ i: last, type: 'arrive' });
    actions.sort((a, b) => a.i - b.i);

    /* ---- helpers for wording ---- */
    const landmarkNear = (node: MapNode, exclude: string[] = []): string | null => {
        let best: MapLocation | null = null;
        let bestD = LANDMARK_RADIUS_UNITS;
        for (const l of data.locations) {
            if (l.floor !== node.floor || exclude.includes(l.id) || l.category === 'transport') continue;
            const d = dist(l, node);
            if (d < bestD) {
                bestD = d;
                best = l;
            }
        }
        return best?.name ?? null;
    };
    const legEndName = (idx: number): string | null => {
        const node = pts[idx];
        const act = actions.find((a) => a.i === idx && (a.type === 'vertical' || a.type === 'arrive'));
        if (act?.type === 'vertical') return act.kind === 'stairs' ? 'the stairs' : 'the elevators';
        if (act?.type === 'arrive') return dest.name;
        return landmarkNear(node, [dest.id, origin.locationId ?? '']);
    };

    /* ---- pass 2: build steps ---- */
    const steps: Step[] = [];
    const transitions: Transition[] = [];

    actions.forEach((act, k) => {
        const nextAct = actions[k + 1];
        // A vertical action's own leg is the ride; the walk after it belongs to the following 'exit' step.
        const legFrom = act.type === 'vertical' ? act.to : act.i;
        const legTo = nextAct ? nextAct.i : act.i;
        let meters = 0;
        let seconds = 0;
        const legPath: Point[] = [{ x: pts[legFrom].x, y: pts[legFrom].y }];
        if (act.type !== 'arrive') {
            for (let n = legFrom; n < legTo; n++) {
                meters += edges[n].meters;
                seconds += edges[n].seconds;
                legPath.push({ x: pts[n + 1].x, y: pts[n + 1].y });
            }
        }

        const node = pts[act.i];
        const base = { index: steps.length, meters, seconds, floor: node.floor, at: { x: node.x, y: node.y }, path: legPath };
        const target = act.type === 'arrive' ? null : legEndName(legTo);

        switch (act.type) {
            case 'start': {
                const kind = act.angle !== null ? turnKind(act.angle) : null;
                const from = origin.locationId ? origin.label : null;
                const towards = target ? ` toward ${target}` : '';
                if (kind) {
                    steps.push({ ...base, kind, text: TURN_TEXT[kind], detail: `${from ? `Leave ${from} and ` : ''}${TURN_TEXT[kind].toLowerCase()}${towards}.` });
                } else {
                    steps.push({ ...base, kind: 'start', text: 'Head straight', detail: `${from ? `Leave ${from} and head` : 'Head'} straight${towards}.` });
                }
                break;
            }
            case 'turn': {
                const at = landmarkNear(node, [dest.id]);
                steps.push({ ...base, kind: act.kind, text: TURN_TEXT[act.kind], detail: `${TURN_TEXT[act.kind]}${at ? ` at ${at}` : ''}${target ? `, toward ${target}` : ''}.` });
                break;
            }
            case 'vertical': {
                const from = node;
                const to = pts[act.to];
                const direction = (graph.level.get(to.floor) ?? 0) > (graph.level.get(from.floor) ?? 0) ? 'up' : 'down';
                const vias = act.kind === 'stairs' ? 'the stairs' : 'the elevator';
                const t: Transition = { floor: from.floor, toFloor: to.floor, x: from.x, y: from.y, kind: act.kind, direction };
                transitions.push(t);
                let rideSeconds = 0;
                for (let n = act.i; n < act.to; n++) rideSeconds += edges[n].seconds;
                steps.push({
                    ...base,
                    meters: 0,
                    seconds: rideSeconds,
                    path: [],
                    kind: act.kind,
                    text: act.kind === 'stairs' ? `Take the stairs ${direction}` : 'Take the elevator',
                    detail: `Use ${vias} to go ${direction} to the ${floorName(to.floor)}.`,
                    transition: t,
                });
                break;
            }
            case 'exit': {
                const what = act.via === 'stairs' ? 'the stairs' : 'the elevator';
                const kind = act.kind && act.kind !== 'uturn' ? act.kind : null;
                const towards = target ? ` toward ${target}` : '';
                steps.push({
                    ...base,
                    kind: kind ?? 'exit',
                    text: kind ? TURN_TEXT[kind] : 'Head straight',
                    detail: kind ? `After exiting ${what}, ${TURN_TEXT[kind].toLowerCase()}${towards}.` : `Exit ${what} and head straight${towards}.`,
                });
                break;
            }
            case 'arrive': {
                const back = last > 0 && edges[last - 1].kind === 'walk' ? vec(pts[last - 1], pts[last]) : null;
                // Entering the room: along the walkway to the door, then straight in.
                const toDest: Point = Math.abs(dest.x - pts[last].x) > 8 && Math.abs(dest.y - pts[last].y) > 20 ? { x: 0, y: dest.y - pts[last].y } : vec(pts[last], dest);
                let where = 'is right here';
                if (len(toDest) > 20) {
                    if (back) {
                        const side = turnAngle(back, toDest);
                        where = Math.abs(side) < 30 ? 'is straight ahead' : side > 0 ? 'is on your right' : 'is on your left';
                    } else {
                        where = 'is nearby';
                    }
                }
                steps.push({ ...base, meters: 0, seconds: 0, path: [], kind: 'arrive', text: 'You have arrived', detail: `${dest.name} ${where}.` });
                break;
            }
        }
    });

    /* ---- drawable segments, one per stretch on a floor ---- */
    // Floors a lift/stairs merely passes through (e.g. G -> 2 passes 1) are not "visited": no segment, not listed.
    const passThrough = new Set<number>();
    for (const a of actions) if (a.type === 'vertical') for (let n = a.i + 1; n < a.to; n++) passThrough.add(n);
    const kept = pts.map((p, idx) => ({ p, idx })).filter(({ idx }) => !passThrough.has(idx));

    const segments: RouteSegment[] = [];
    const startPoint: Point = len(leaving) > 20 ? { x: origin.x, y: origin.y } : { x: pts[0].x, y: pts[0].y };
    const startElbow: Point[] = elbowStart ? [{ x: origin.x, y: pts[0].y }] : [];
    let cur: RouteSegment = { floor: pts[0].floor, points: [startPoint, ...startElbow, { x: pts[0].x, y: pts[0].y }] };
    for (const { p } of kept.slice(1)) {
        if (p.floor !== cur.floor) {
            segments.push(cur);
            cur = { floor: p.floor, points: [] };
        }
        cur.points.push({ x: p.x, y: p.y });
    }
    if (len(vec(pts[last], dest)) > 20) {
        if (Math.abs(dest.x - pts[last].x) > 8 && Math.abs(dest.y - pts[last].y) > 20) cur.points.push({ x: dest.x, y: pts[last].y });
        cur.points.push({ x: dest.x, y: dest.y });
    }
    segments.push(cur);
    for (const s of segments) s.points = s.points.filter((p, idx, arr) => idx === 0 || dist(p, arr[idx - 1]) > 0.5);

    const floorsVisited = kept.map(({ p }) => p.floor).filter((f, idx, arr) => idx === 0 || f !== arr[idx - 1]);
    return {
        steps,
        segments,
        transitions,
        meters: steps.reduce((s, st) => s + st.meters, 0),
        seconds: steps.reduce((s, st) => s + st.seconds, 0),
        floors: floorsVisited,
    };
}

/* --------------------------------- formatting --------------------------------- */

export function formatDistance(meters: number): string {
    if (meters < 1) return '';
    return meters < 1000 ? `${Math.round(meters)} m` : `${(meters / 1000).toFixed(1)} km`;
}

export function formatDuration(seconds: number): string {
    const minutes = Math.max(1, Math.round(seconds / 60));
    return `${minutes} min`;
}
