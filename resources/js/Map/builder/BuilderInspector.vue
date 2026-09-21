<script setup lang="ts">
import { computed } from 'vue';
import { CATEGORY_META } from '../categories';
import type { AreaKind, LocationCategory, NodeType } from '../types';
import type { Builder, Side } from './useMapBuilder';

export interface ContentOption {
    type: 'facility' | 'restaurant' | 'room';
    slug: string;
    name: string;
}

const props = defineProps<{ b: Builder; content: ContentOption[] }>();
const b = props.b;

const AREA_LABELS: Record<AreaKind, string> = {
    building: 'Building outline', corridor: 'Corridor / hallway', room: 'Room', public: 'Public space', service: 'Service / staff only', water: 'Pool / water', outdoor: 'Outdoor',
};
const NODE_LABELS: Record<NodeType, string> = { walk: 'Walkway point', entrance: 'Entrance', elevator: 'Elevator', stairs: 'Stairs' };
const SIDES: { v: Side; l: string }[] = [{ v: 'n', l: 'North (up)' }, { v: 's', l: 'South (down)' }, { v: 'w', l: 'West (left)' }, { v: 'e', l: 'East (right)' }];

const val = (e: Event) => (e.target as HTMLInputElement).value;
const num = (e: Event) => Number(val(e));
const isLift = computed(() => b.selectedNode.value && (b.selectedNode.value.type === 'elevator' || b.selectedNode.value.type === 'stairs'));
const refValue = computed(() => (b.selectedLocation.value?.ref ? `${b.selectedLocation.value.ref.type}:${b.selectedLocation.value.ref.slug}` : ''));
const shaftFloors = computed(() => (b.selectedNode.value ? new Set(b.shaftOf(b.selectedNode.value.id).map((n) => n.floor)) : new Set<string>()));
const doorNode = computed(() => (b.selectedLocation.value?.node ? b.node(b.selectedLocation.value.node) : null));

function setRef(v: string) {
    const l = b.selectedLocation.value;
    if (!l) return;
    if (!v) return b.updateLocation(l.id, { ref: null });
    const [type, slug] = v.split(':');
    b.updateLocation(l.id, { ref: { type: type as 'facility', slug } });
}

const field = 'w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm';
const label = 'mb-1 mt-3 block text-xs font-medium text-slate-500';
const danger = 'mt-5 w-full rounded-md border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50';
</script>

<template>
    <div class="text-slate-800" data-testid="inspector">
        <!-- ================= a place ================= -->
        <template v-if="b.selectedLocation.value">
            <h3 class="text-sm font-semibold">Place</h3>
            <label :class="label">Name</label>
            <input :value="b.selectedLocation.value.name" :class="field" data-testid="loc-name" @change="b.updateLocation(b.selectedLocation.value!.id, { name: val($event) })" />

            <label :class="label">Type</label>
            <select :value="b.selectedLocation.value.category" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { category: val($event) as LocationCategory })">
                <option v-for="(m, c) in CATEGORY_META" :key="c" :value="c">{{ m.icon }} {{ m.label }}</option>
            </select>

            <label :class="label">Description</label>
            <textarea :value="b.selectedLocation.value.description ?? ''" rows="3" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { description: val($event) })" />

            <label :class="label">Opening hours (optional)</label>
            <input :value="b.selectedLocation.value.opening_hours ?? ''" placeholder="e.g. 07:00 - 22:00" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { opening_hours: val($event) })" />

            <label :class="label">Show content from your site</label>
            <select :value="refValue" :class="field" data-testid="loc-ref" @change="setRef(val($event))">
                <option value="">— none —</option>
                <option v-for="c in content" :key="c.type + c.slug" :value="`${c.type}:${c.slug}`">{{ c.name }} ({{ c.type }})</option>
            </select>
            <p class="mt-1 text-xs text-slate-400">Adds the photo, text and "View Details" link from that page (published pages only).</p>

            <label :class="label">Photo URL (optional)</label>
            <input :value="b.selectedLocation.value.image ?? ''" placeholder="https://…" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { image: val($event) })" />

            <label class="mt-4 flex items-center gap-2 text-sm">
                <input type="checkbox" :checked="b.data.value.default_start === b.selectedLocation.value.id" data-testid="loc-kiosk" @change="b.setDefaultStart(($event.target as HTMLInputElement).checked ? b.selectedLocation.value!.id : null)" />
                This is where the kiosk is ("You are here")
            </label>

            <div class="mt-4 rounded-md bg-slate-50 p-2.5 text-xs text-slate-500">
                <b class="text-slate-600">Door:</b>
                {{ doorNode ? 'joined to a walkway point (dashed blue line on the map).' : 'no walkway on this floor yet.' }}
                It picks the nearest one and follows the place when you move it.
                <button v-if="!b.doorPick.value" type="button" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" data-testid="pick-door" @click="b.doorPick.value = b.selectedLocation.value!.id">Choose the door on the map</button>
                <span v-else class="mt-2 block rounded-md bg-sky-50 p-2 text-sky-700">Click the walkway point that is this place's door.
                    <button type="button" class="ml-1 underline" @click="b.doorPick.value = null">Cancel</button></span>
            </div>
            <button :class="danger" data-testid="delete" @click="b.remove()">Delete this place</button>
        </template>

        <!-- ================= a walkway point / lift / stairs ================= -->
        <template v-else-if="b.selectedNode.value">
            <h3 class="text-sm font-semibold">{{ NODE_LABELS[b.selectedNode.value.type] }}</h3>
            <label :class="label">Kind of point</label>
            <select :value="b.selectedNode.value.type" :class="field" data-testid="node-type" @change="b.setNodeType(b.selectedNode.value!.id, val($event) as NodeType)">
                <option v-for="(l, t) in NODE_LABELS" :key="t" :value="t">{{ l }}</option>
            </select>
            <p class="mt-2 text-xs text-slate-400">Connected to {{ b.selectedNode.value.connections.length }} other point(s). Use the Walkway tool to add more.</p>

            <template v-if="isLift">
                <label :class="label">Where is the {{ b.selectedNode.value.type === 'stairs' ? 'staircase' : 'lift' }} relative to the walkway?</label>
                <select :value="b.liftSide(b.selectedNode.value)" :class="field" data-testid="lift-side" @change="b.setLiftSide(b.selectedNode.value!.id, val($event) as Side)">
                    <option v-for="s in SIDES" :key="s.v" :value="s.v">{{ s.l }}</option>
                </select>
                <p class="mt-1 text-xs text-slate-400">Used to say "turn left / right after exiting".</p>

                <div class="mt-4 rounded-md border border-slate-200 p-3">
                    <p class="text-xs font-medium text-slate-600">Floors it connects</p>
                    <ul class="mt-1.5 space-y-0.5 text-sm">
                        <li v-for="f in b.floors.value" :key="f.id" class="flex items-center gap-2">
                            <span :class="shaftFloors.has(f.id) ? 'text-emerald-600' : 'text-slate-300'">{{ shaftFloors.has(f.id) ? '●' : '○' }}</span>
                            {{ f.name }}
                        </li>
                    </ul>
                    <button class="mt-3 w-full rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900" data-testid="link-shaft" @click="b.linkShaft(b.selectedNode.value!.id)">Connect to all floors</button>
                    <button v-if="shaftFloors.size > 1" class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="b.unlinkShaft(b.selectedNode.value!.id)">Disconnect from other floors</button>
                    <p class="mt-2 text-xs text-slate-400">Missing floors get a matching {{ b.selectedNode.value.type === 'stairs' ? 'staircase' : 'lift' }} in the same spot; join it to that floor's walkway.</p>
                </div>
            </template>

            <button :class="danger" data-testid="delete" @click="b.remove()">Delete this point</button>
        </template>

        <!-- ================= a room / area ================= -->
        <template v-else-if="b.selectedArea.value">
            <h3 class="text-sm font-semibold">{{ AREA_LABELS[b.selectedArea.value.kind] }}</h3>
            <label :class="label">Kind</label>
            <select :value="b.selectedArea.value.kind" :class="field" data-testid="area-kind" @change="b.updateArea(b.selectedArea.value!.id, { kind: val($event) as AreaKind })">
                <option v-for="(l, k) in AREA_LABELS" :key="k" :value="k">{{ l }}</option>
            </select>
            <label :class="label">Label (shown on the plan in the editor)</label>
            <input :value="b.selectedArea.value.label ?? ''" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { label: val($event) })" />
            <div class="mt-3 grid grid-cols-2 gap-2">
                <label class="text-xs text-slate-500">X<input type="number" :value="b.selectedArea.value.x" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { x: num($event) })" /></label>
                <label class="text-xs text-slate-500">Y<input type="number" :value="b.selectedArea.value.y" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { y: num($event) })" /></label>
                <label class="text-xs text-slate-500">Width<input type="number" :value="b.selectedArea.value.w" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { w: num($event) })" /></label>
                <label class="text-xs text-slate-500">Height<input type="number" :value="b.selectedArea.value.h" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { h: num($event) })" /></label>
            </div>
            <p class="mt-2 text-xs text-slate-400">Tip: drag the round handles on the corners to resize.</p>
            <button :class="danger" data-testid="delete" @click="b.remove()">Delete this area</button>
        </template>

        <!-- ================= nothing selected: the floor ================= -->
        <template v-else>
            <h3 class="text-sm font-semibold">This floor</h3>
            <div class="grid grid-cols-[5rem_1fr] gap-2">
                <div><label :class="label">Short name</label><input :value="b.floor.value.label" maxlength="4" :class="field" data-testid="floor-label" @change="b.updateFloor(b.floorId.value, { label: val($event) || b.floor.value.label })" /></div>
                <div><label :class="label">Full name</label><input :value="b.floor.value.name" :class="field" data-testid="floor-name" @change="b.updateFloor(b.floorId.value, { name: val($event) || b.floor.value.name })" /></div>
            </div>
            <label :class="label">Level (−1 basement, 0 ground, 1, 2 …)</label>
            <input type="number" :value="b.floor.value.level" :class="field" @change="b.updateFloor(b.floorId.value, { level: num($event) })" />
            <label :class="label">Floor-plan image URL (optional)</label>
            <input :value="b.floor.value.plan_image ?? ''" placeholder="https://… (drawn under your rooms)" :class="field" @change="b.updateFloor(b.floorId.value, { plan_image: val($event) })" />
            <p class="mt-1 text-xs text-slate-400">Trace over a real floor plan: paste its image address, then draw rooms and walkways on top.</p>

            <div class="mt-5 rounded-md bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">
                <p class="font-medium text-slate-600">How to build a map</p>
                <ol class="mt-1 list-decimal space-y-1 pl-4">
                    <li><b>Area</b> tool: drag to draw rooms, halls and outlines.</li>
                    <li><b>Walkway</b> tool: click along the corridors. Click a point to continue from it.</li>
                    <li><b>Place</b> tool: click inside a room to add a restaurant, room, spa…</li>
                    <li>Select a lift → <i>Connect to all floors</i>.</li>
                    <li>Tick <i>kiosk</i> on the place where the screen is, then test a route below.</li>
                </ol>
            </div>
        </template>
    </div>
</template>
