<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { Camera } from '../camera';
import { AREA_STYLE, CATEGORY_META, ROUTE_COLOR, YOU_COLOR } from '../categories';
import type { Point, Route } from '../types';
import type { Builder } from './useMapBuilder';

const props = defineProps<{ b: Builder; camera: Camera; route: Route | null }>();
const b = props.b;
const cam = props.camera;
const root = ref<HTMLElement | null>(null);
const k = computed(() => 1 / cam.zoom.value); // handles & markers keep a constant on-screen size

const NODE_COLOR = { walk: '#14805e', entrance: '#183c2d', elevator: '#2b6fd6', stairs: '#b5573a' } as const;
const NODE_GLYPH = { walk: '', entrance: '⇥', elevator: '⇅', stairs: '≡' } as const;

let ro: ResizeObserver | null = null;
onMounted(() => {
    ro = new ResizeObserver(([e]) => {
        cam.width.value = e.contentRect.width;
        cam.height.value = e.contentRect.height;
    });
    if (root.value) ro.observe(root.value);
});
onBeforeUnmount(() => ro?.disconnect());

/* ------------------------------ gestures ------------------------------ */
type Drag =
    | { mode: 'pan' }
    | { mode: 'move'; kind: 'area' | 'node' | 'location'; id: string; dx: number; dy: number; moved: boolean }
    | { mode: 'resize'; id: string; handle: string; x: number; y: number; w: number; h: number }
    | { mode: 'draw'; x0: number; y0: number }
    | { mode: 'click'; el: HTMLElement | null }
    | null;

let drag: Drag = null;
const pointers = new Map<number, Point>();
let pinch = 0;
let downPx: Point = { x: 0, y: 0 };
let movedPx = false;
let last: Point = { x: 0, y: 0 };
const draft = ref<{ x: number; y: number; w: number; h: number } | null>(null);
const cursor = ref<Point | null>(null);

const px = (e: PointerEvent | WheelEvent) => {
    const r = root.value!.getBoundingClientRect();
    return { x: e.clientX - r.left, y: e.clientY - r.top };
};
const world = (e: PointerEvent) => cam.toWorld(px(e).x, px(e).y);
const isEl = (t: EventTarget | null) => (t as Element | null)?.closest?.('[data-el]') as HTMLElement | null;

function onDown(e: PointerEvent) {
    root.value?.setPointerCapture?.(e.pointerId);
    const p = px(e);
    pointers.set(e.pointerId, p);
    cam.stop();
    if (pointers.size === 2) {
        const [a, c] = [...pointers.values()];
        pinch = Math.hypot(a.x - c.x, a.y - c.y);
        drag = { mode: 'pan' };
        draft.value = null;
        return;
    }
    downPx = p;
    last = p;
    movedPx = false;

    const w = world(e);
    const el = isEl(e.target);
    const handle = ((e.target as Element).closest('[data-handle]') as HTMLElement | null)?.dataset;
    const tool = b.tool.value;

    if (e.button === 1 || e.button === 2) return void (drag = { mode: 'pan' });

    if (tool === 'select') {
        if (b.doorPick.value && el?.dataset.el === 'node') {
            const who = b.doorPick.value;
            b.setDoor(who, el.dataset.id!);
            return void (b.selection.value = { kind: 'location', id: who });
        }
        if (handle?.handle) {
            const a = b.area(handle.id!)!;
            return void (drag = { mode: 'resize', id: a.id, handle: handle.handle, x: a.x, y: a.y, w: a.w, h: a.h });
        }
        if (el) {
            const kind = el.dataset.el as 'area' | 'node' | 'location';
            const id = el.dataset.id!;
            b.selection.value = { kind, id };
            const at = kind === 'area' ? b.area(id)! : kind === 'node' ? b.node(id)! : b.location(id)!;
            return void (drag = { mode: 'move', kind, id, dx: w.x - at.x, dy: w.y - at.y, moved: false });
        }
        b.selection.value = null;
        return void (drag = { mode: 'pan' });
    }
    if (tool === 'area') {
        const s = b.snapPoint(w);
        draft.value = { x: s.x, y: s.y, w: 0, h: 0 };
        return void (drag = { mode: 'draw', x0: s.x, y0: s.y });
    }
    // location + path tools: a click acts, a drag pans
    drag = { mode: 'click', el };
}

function onMove(e: PointerEvent) {
    const cur = px(e);
    cursor.value = cam.toWorld(cur.x, cur.y);
    if (!pointers.has(e.pointerId)) return;
    pointers.set(e.pointerId, cur);

    if (pointers.size === 2) {
        const [a, c] = [...pointers.values()];
        const d = Math.hypot(a.x - c.x, a.y - c.y);
        if (pinch > 0) cam.zoomAt(d / pinch, (a.x + c.x) / 2, (a.y + c.y) / 2);
        pinch = d;
        return;
    }
    if (!drag) return;
    if (!movedPx && Math.hypot(cur.x - downPx.x, cur.y - downPx.y) > 5) movedPx = true;
    const w = cam.toWorld(cur.x, cur.y);

    switch (drag.mode) {
        case 'pan':
            cam.panByPx(cur.x - last.x, cur.y - last.y);
            break;
        case 'click':
            if (movedPx) cam.panByPx(cur.x - last.x, cur.y - last.y);
            break;
        case 'move': {
            if (!drag.moved) {
                if (!movedPx) break;
                drag.moved = true;
                b.beginGesture();
            }
            const nx = w.x - drag.dx;
            const ny = w.y - drag.dy;
            if (drag.kind === 'area') {
                const s = b.snapPoint({ x: nx, y: ny });
                b.moveArea(drag.id, s.x, s.y);
            } else if (drag.kind === 'node') {
                const s = b.snapPoint({ x: nx, y: ny }, { radius: 10 * k.value, nodes: true, exclude: drag.id });
                b.moveNode(drag.id, s.x, s.y);
            } else {
                const s = b.snapPoint({ x: nx, y: ny });
                b.moveLocation(drag.id, s.x, s.y);
            }
            break;
        }
        case 'resize': {
            if (!movedPx) break;
            b.beginGesture();
            const s = b.snapPoint(w);
            const r = drag;
            let x1 = r.x;
            let y1 = r.y;
            let x2 = r.x + r.w;
            let y2 = r.y + r.h;
            if (r.handle.includes('w')) x1 = Math.min(s.x, x2 - 20);
            if (r.handle.includes('e')) x2 = Math.max(s.x, x1 + 20);
            if (r.handle.includes('n')) y1 = Math.min(s.y, y2 - 20);
            if (r.handle.includes('s')) y2 = Math.max(s.y, y1 + 20);
            b.resizeArea(r.id, x1, y1, x2 - x1, y2 - y1);
            break;
        }
        case 'draw': {
            const s = b.snapPoint(w);
            draft.value = { x: Math.min(drag.x0, s.x), y: Math.min(drag.y0, s.y), w: Math.abs(s.x - drag.x0), h: Math.abs(s.y - drag.y0) };
            break;
        }
    }
    last = cur;
}

function onUp(e: PointerEvent) {
    const was = drag;
    const wasSingle = pointers.size === 1;
    pointers.delete(e.pointerId);
    if (pointers.size < 2) pinch = 0;
    b.endGesture();
    drag = null;
    if (!wasSingle || !was) return;

    if (was.mode === 'draw' && draft.value) {
        const d = draft.value;
        draft.value = null;
        b.addArea(d.x, d.y, d.w, d.h);
    } else if (was.mode === 'click' && !movedPx) {
        click(world(e), was.el);
    }
}

/** A click (not a drag) with the place / walkway tools. */
function click(w: Point, el: HTMLElement | null) {
    if (b.tool.value === 'location') {
        if (el?.dataset.el === 'location') return void (b.selection.value = { kind: 'location', id: el.dataset.id! });
        const s = b.snapPoint(w);
        b.addLocation(s.x, s.y);
        return;
    }
    if (b.tool.value === 'path') {
        const hit = el?.dataset.el === 'node' ? el.dataset.id! : null;
        if (hit) {
            if (b.pathFrom.value && b.pathFrom.value !== hit) b.connect(b.pathFrom.value, hit);
            b.pathFrom.value = hit;
            b.selection.value = { kind: 'node', id: hit };
            return;
        }
        const s = b.snapPoint(w, { radius: 12 * k.value, nodes: true });
        const p = b.orthoFrom(b.pathFrom.value, s);
        const id = b.addNode(p.x, p.y, b.pathNodeType.value, b.pathFrom.value);
        b.pathFrom.value = id;
        b.selection.value = { kind: 'node', id };
    }
}

function onWheel(e: WheelEvent) {
    e.preventDefault();
    const p = px(e);
    cam.zoomAt(Math.exp(-e.deltaY * 0.0015), p.x, p.y);
}

/* ------------------------------ drawing helpers ------------------------------ */
const sel = computed(() => b.selection.value);
const isSel = (kind: string, id: string) => sel.value?.kind === kind && sel.value.id === id;

/** Every walkway connection on this floor, once. */
const edges = computed(() => {
    const out: { key: string; a: { x: number; y: number }; c: { x: number; y: number } }[] = [];
    const seen = new Set<string>();
    for (const n of b.nodesHere.value) {
        for (const id of n.connections) {
            const o = b.nodeMap.value.get(id);
            if (!o || o.floor !== n.floor) continue;
            const key = n.id < id ? `${n.id}|${id}` : `${id}|${n.id}`;
            if (seen.has(key)) continue;
            seen.add(key);
            out.push({ key, a: n, c: o });
        }
    }
    return out;
});
const vertical = (id: string) => b.node(id)!.connections.some((c) => b.node(c)?.floor !== b.node(id)!.floor);

const routeSeg = computed(() => props.route?.segments.find((s) => s.floor === b.floorId.value) ?? null);
const routeD = computed(() => routeSeg.value?.points.map((p, i) => `${i ? 'L' : 'M'}${p.x} ${p.y}`).join(' ') ?? '');

const pending = computed(() => {
    const from = b.pathFrom.value ? b.node(b.pathFrom.value) : null;
    if (!from || b.tool.value !== 'path' || !cursor.value || from.floor !== b.floorId.value) return null;
    const s = b.snapPoint(cursor.value, { radius: 12 * k.value, nodes: true });
    return { from, to: b.orthoFrom(from.id, s) };
});

const HANDLES = [
    ['nw', 0, 0], ['ne', 1, 0], ['sw', 0, 1], ['se', 1, 1],
] as const;
const pe = (kinds: string[]) => (kinds.includes(b.tool.value) ? 'auto' : 'none');
</script>

<template>
    <div
        ref="root"
        class="absolute inset-0 touch-none select-none overflow-hidden bg-[#e9e2d3]"
        :class="{ 'cursor-crosshair': b.tool.value !== 'select' }"
        data-testid="builder-canvas"
        @pointerdown="onDown"
        @pointermove="onMove"
        @pointerup="onUp"
        @pointercancel="onUp"
        @pointerleave="cursor = null"
        @wheel="onWheel"
        @contextmenu.prevent
    >
        <svg class="absolute inset-0 h-full w-full" :viewBox="cam.viewBox.value" preserveAspectRatio="none">
            <defs>
                <pattern id="bgrid" width="50" height="50" patternUnits="userSpaceOnUse">
                    <path d="M50 0H0V50" fill="none" stroke="#d8cfba" stroke-width="0.6" />
                </pattern>
            </defs>

            <!-- the floor -->
            <rect :width="b.floor.value.width" :height="b.floor.value.height" fill="#f5f1e8" pointer-events="none" />
            <image v-if="b.floor.value.plan_image" :href="b.floor.value.plan_image" x="0" y="0" :width="b.floor.value.width" :height="b.floor.value.height" preserveAspectRatio="none" pointer-events="none" opacity="0.85" />
            <rect v-if="b.snapOn.value" :width="b.floor.value.width" :height="b.floor.value.height" fill="url(#bgrid)" pointer-events="none" />
            <rect :width="b.floor.value.width" :height="b.floor.value.height" fill="none" stroke="#bfb08e" stroke-width="2" stroke-dasharray="8 6" pointer-events="none" />

            <!-- rooms & areas -->
            <g>
                <rect
                    v-for="a in b.floor.value.areas"
                    :key="a.id"
                    :x="a.x" :y="a.y" :width="a.w" :height="a.h"
                    :rx="a.kind === 'building' ? 14 : a.kind === 'corridor' ? 0 : 6"
                    :fill="AREA_STYLE[a.kind].fill"
                    :fill-opacity="b.floor.value.plan_image ? 0.75 : 1"
                    :stroke="isSel('area', a.id) ? '#2b6fd6' : AREA_STYLE[a.kind].stroke === 'none' ? '#e3dac6' : AREA_STYLE[a.kind].stroke"
                    :stroke-width="isSel('area', a.id) ? 3 * k : a.kind === 'building' ? 3 : 1.5"
                    data-el="area" :data-id="a.id"
                    :pointer-events="a.kind === 'building' ? (b.tool.value === 'select' ? 'visibleStroke' : 'none') : pe(['select'])"
                />
                <text v-for="a in b.floor.value.areas.filter((x) => x.label && x.kind !== 'building' && x.kind !== 'corridor')" :key="`l-${a.id}`" :x="a.x + a.w / 2" :y="a.y + 18" text-anchor="middle" :font-size="12" fill="#8a7d63" pointer-events="none">{{ a.label }}</text>
            </g>

            <!-- walkways -->
            <g pointer-events="none">
                <line v-for="e in edges" :key="e.key" :x1="e.a.x" :y1="e.a.y" :x2="e.c.x" :y2="e.c.y" stroke="#14805e" :stroke-width="4 * k" stroke-linecap="round" opacity="0.55" />
            </g>
            <path v-if="routeD" :d="routeD" fill="none" :stroke="ROUTE_COLOR" :stroke-width="7 * k" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="1 12" pointer-events="none" data-testid="test-route" />
            <line v-if="pending" :x1="pending.from.x" :y1="pending.from.y" :x2="pending.to.x" :y2="pending.to.y" stroke="#14805e" :stroke-width="3 * k" stroke-dasharray="6 6" pointer-events="none" />

            <!-- walkway points -->
            <g
                v-for="n in b.nodesHere.value"
                :key="n.id"
                :transform="`translate(${n.x} ${n.y}) scale(${k})`"
                data-el="node" :data-id="n.id"
                :pointer-events="pe(['select', 'path'])"
                class="cursor-pointer"
            >
                <circle r="16" fill="transparent" />
                <circle v-if="isSel('node', n.id) || b.pathFrom.value === n.id" r="13" fill="none" stroke="#2b6fd6" stroke-width="3" />
                <circle v-if="b.doorPick.value" r="16" fill="rgba(43,111,214,0.18)" stroke="#2b6fd6" stroke-width="2" stroke-dasharray="3 3" />
                <circle :r="n.type === 'walk' ? 6.5 : 11" :fill="NODE_COLOR[n.type]" stroke="#fff" stroke-width="2.5" />
                <text v-if="NODE_GLYPH[n.type]" text-anchor="middle" dominant-baseline="central" font-size="12" fill="#fff" pointer-events="none">{{ NODE_GLYPH[n.type] }}</text>
                <text v-if="vertical(n.id)" y="-17" text-anchor="middle" font-size="11" font-weight="700" :fill="NODE_COLOR[n.type]" pointer-events="none">linked</text>
                <line v-if="n.door && isSel('node', n.id)" x1="0" y1="0" :x2="(n.door.x - n.x) * cam.zoom.value" :y2="(n.door.y - n.y) * cam.zoom.value" stroke="#2b6fd6" stroke-width="2" stroke-dasharray="3 3" pointer-events="none" />
            </g>

            <line v-if="b.selectedLocation.value && b.selectedLocation.value.floor === b.floorId.value && b.selectedLocation.value.node && b.node(b.selectedLocation.value.node)" :x1="b.selectedLocation.value.x" :y1="b.selectedLocation.value.y" :x2="b.node(b.selectedLocation.value.node)!.x" :y2="b.node(b.selectedLocation.value.node)!.y" stroke="#2b6fd6" :stroke-width="2 * k" stroke-dasharray="4 4" pointer-events="none" />

            <!-- places -->
            <g
                v-for="l in b.locationsHere.value"
                :key="l.id"
                :transform="`translate(${l.x} ${l.y}) scale(${k})`"
                data-el="location" :data-id="l.id"
                :pointer-events="pe(['select', 'location'])"
                class="cursor-pointer"
            >
                <circle r="24" fill="transparent" />
                <circle v-if="isSel('location', l.id)" r="24" fill="none" stroke="#2b6fd6" stroke-width="3" />
                <circle cy="2" r="16" fill="rgba(0,0,0,0.16)" />
                <circle r="16" :fill="CATEGORY_META[l.category].color" stroke="#fff" stroke-width="3" />
                <text text-anchor="middle" dominant-baseline="central" font-size="15" fill="#fff" pointer-events="none">{{ CATEGORY_META[l.category].icon }}</text>
                <text y="32" text-anchor="middle" font-size="13" font-weight="600" fill="#20241f" stroke="#fbf9f4" stroke-width="4" paint-order="stroke" pointer-events="none">{{ l.name }}</text>
                <circle v-if="b.data.value.default_start === l.id" cx="15" cy="-15" r="9" :fill="YOU_COLOR" stroke="#fff" stroke-width="2" pointer-events="none" />
            </g>

            <!-- rectangle being drawn -->
            <rect v-if="draft" :x="draft.x" :y="draft.y" :width="draft.w" :height="draft.h" fill="rgba(43,111,214,0.15)" stroke="#2b6fd6" :stroke-width="2 * k" stroke-dasharray="6 4" pointer-events="none" />

            <!-- resize handles -->
            <template v-if="b.selectedArea.value && b.tool.value === 'select' && b.selectedArea.value.kind !== 'building'">
                <g v-for="[h, ax, ay] in HANDLES" :key="h" :transform="`translate(${b.selectedArea.value.x + ax * b.selectedArea.value.w} ${b.selectedArea.value.y + ay * b.selectedArea.value.h}) scale(${k})`" :data-handle="h" :data-id="b.selectedArea.value.id" class="cursor-nwse-resize">
                    <circle r="14" fill="transparent" />
                    <circle r="6.5" fill="#fff" stroke="#2b6fd6" stroke-width="2.5" />
                </g>
            </template>
        </svg>
    </div>
</template>
