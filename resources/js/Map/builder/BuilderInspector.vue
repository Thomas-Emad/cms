<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from '@/i18n';
import { CATEGORY_META } from '../categories';
import type { AreaKind, LocationCategory, NodeType } from '../types';
import type { Builder, Side } from './useMapBuilder';

export interface ContentOption {
    type: 'facility' | 'restaurant' | 'room' | 'page';
    slug: string;
    name: string;
    /** Guests can only open published content. */
    published: boolean;
}

const props = defineProps<{ b: Builder; content: ContentOption[] }>();
const b = props.b;
const { t } = useI18n();

const AREA_LABELS: Record<AreaKind, string> = {
    building: 'Building outline', corridor: 'Corridor / hallway', room: 'Room', public: 'Public space', service: 'Service / staff only', water: 'Pool / water', outdoor: 'Outdoor',
};
const NODE_LABELS: Record<NodeType, string> = { walk: 'Walkway point', entrance: 'Entrance', elevator: 'Elevator', stairs: 'Stairs' };
const SIDES: { v: Side; l: string; key: string }[] = [
    { v: 'n', l: 'North (up)', key: 'admin.map.sides.n' },
    { v: 's', l: 'South (down)', key: 'admin.map.sides.s' },
    { v: 'w', l: 'West (left)', key: 'admin.map.sides.w' },
    { v: 'e', l: 'East (right)', key: 'admin.map.sides.e' },
];

const val = (e: Event) => (e.target as HTMLInputElement).value;
const num = (e: Event) => Number(val(e));
const isLift = computed(() => b.selectedNode.value && (b.selectedNode.value.type === 'elevator' || b.selectedNode.value.type === 'stairs'));
const refValue = computed(() => (b.selectedLocation.value?.ref ? `${b.selectedLocation.value.ref.type}:${b.selectedLocation.value.ref.slug}` : ''));
const shaftFloors = computed(() => (b.selectedNode.value ? new Set(b.shaftOf(b.selectedNode.value.id).map((n) => n.floor)) : new Set<string>()));
const TYPE_KEYS = {
    facility: 'admin.map.content_types.facility',
    restaurant: 'admin.map.content_types.restaurant',
    room: 'admin.map.content_types.room',
    page: 'admin.map.content_types.page',
} as const;
const TYPE_PLURAL_KEYS = {
    facility: 'admin.map.content_types.facilities',
    restaurant: 'admin.map.content_types.restaurants',
    room: 'admin.map.content_types.rooms',
    page: 'admin.map.content_types.pages',
} as const;
const groups = computed(() => (['facility', 'restaurant', 'room', 'page'] as const).map((type) => ({ type, label: t(TYPE_PLURAL_KEYS[type], undefined, type), items: props.content.filter((c) => c.type === type) })).filter((g) => g.items.length));
const currentRef = computed(() => { const r = b.selectedLocation.value?.ref; return r ? props.content.find((c) => c.type === r.type && c.slug === r.slug) ?? null : null; });
/** A page/facility/restaurant with the same name as this place: offer to link it in one click. */
const suggestion = computed(() => {
    const l = b.selectedLocation.value;
    if (!l || l.ref || l.link || !l.name.trim()) return null;
    const n = l.name.trim().toLowerCase();
    return props.content.find((c) => c.published && c.name.trim().toLowerCase() === n) ?? null;
});
const doorNode = computed(() => (b.selectedLocation.value?.node ? b.node(b.selectedLocation.value.node) : null));

function setRef(v: string) {
    const l = b.selectedLocation.value;
    if (!l) return;
    if (!v) return b.updateLocation(l.id, { ref: null });
    const [type, slug] = v.split(':');
    b.updateLocation(l.id, { ref: { type: type as ContentOption['type'], slug } });
}

const field = 'w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm';
const label = 'mb-1 mt-3 block text-xs font-medium text-slate-500';
const danger = 'mt-5 w-full rounded-md border border-red-200 px-3 py-2 text-sm text-red-600 hover:bg-red-50';
</script>

<template>
    <div class="text-slate-800" data-testid="inspector">
        <!-- ================= a place ================= -->
        <template v-if="b.selectedLocation.value">
            <h3 class="text-sm font-semibold">{{ $t('admin.map.inspector_place') }}</h3>
            <label :class="label">{{ $t('admin.map.inspector_name') }}</label>
            <input :value="b.selectedLocation.value.name" :class="field" data-testid="loc-name" @change="b.updateLocation(b.selectedLocation.value!.id, { name: val($event) })" />

            <label :class="label">{{ $t('admin.map.inspector_type') }}</label>
            <select :value="b.selectedLocation.value.category" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { category: val($event) as LocationCategory })">
                <option v-for="(m, c) in CATEGORY_META" :key="c" :value="c">{{ m.icon }} {{ $t('map.categories.' + c) || m.label }}</option>
            </select>

            <label :class="label">{{ $t('admin.map.inspector_desc') }}</label>
            <textarea :value="b.selectedLocation.value.description ?? ''" rows="3" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { description: val($event) })" />

            <label :class="label">{{ $t('admin.map.inspector_hours') }}</label>
            <input :value="b.selectedLocation.value.opening_hours ?? ''" :placeholder="$t('admin.map.hours_placeholder')" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { opening_hours: val($event) })" />

            <label :class="label">{{ $t('admin.map.view_details_opens') }}</label>
            <select :value="refValue" :class="field" data-testid="loc-ref" @change="setRef(val($event))">
                <option value="">{{ $t('admin.map.ref_nothing') }}</option>
                <optgroup v-for="g in groups" :key="g.type" :label="g.label">
                    <option v-for="c in g.items" :key="c.type + c.slug" :value="`${c.type}:${c.slug}`">{{ c.name }}{{ c.published ? '' : `  ${$t('admin.map.not_published')}` }}</option>
                </optgroup>
            </select>
            <p v-if="currentRef && !currentRef.published" class="mt-1 rounded bg-amber-50 px-2 py-1 text-xs text-amber-700" data-testid="ref-unpublished">{{ $t('admin.map.ref_unpublished_warning', { name: currentRef.name }) }}</p>
            <p v-else-if="b.selectedLocation.value.ref && !currentRef" class="mt-1 rounded bg-amber-50 px-2 py-1 text-xs text-amber-700" data-testid="ref-missing">{{ $t('admin.map.ref_missing_warning') }}</p>
            <button v-if="suggestion" type="button" class="mt-1.5 w-full rounded-md border border-sky-200 bg-sky-50 px-2 py-1.5 text-left text-xs text-sky-800 hover:bg-sky-100 rtl:text-right" data-testid="ref-suggest" @click="setRef(`${suggestion.type}:${suggestion.slug}`)">
                {{ $t('admin.map.link_suggestion', { type: t(TYPE_KEYS[suggestion.type]).toLowerCase(), name: suggestion.name }) }}
            </button>
            <p class="mt-1 text-xs text-slate-400">{{ $t('admin.map.link_suggestion_hint') }}</p>

            <label :class="label">{{ $t('admin.map.custom_url') }}</label>
            <input :value="b.selectedLocation.value.link ?? ''" placeholder="/pages/spa-menu  or  https://…" :class="field" data-testid="loc-link" @change="b.updateLocation(b.selectedLocation.value!.id, { link: val($event) })" />
            <p class="mt-1 text-xs text-slate-400">{{ $t('admin.map.custom_url_hint') }}</p>

            <label :class="label">{{ $t('admin.map.photo_url') }}</label>
            <input :value="b.selectedLocation.value.image ?? ''" placeholder="https://…" :class="field" @change="b.updateLocation(b.selectedLocation.value!.id, { image: val($event) })" />

            <label class="mt-4 flex items-center gap-2 text-sm">
                <input type="checkbox" :checked="b.data.value.default_start === b.selectedLocation.value.id" data-testid="loc-kiosk" @change="b.setDefaultStart(($event.target as HTMLInputElement).checked ? b.selectedLocation.value!.id : null)" />
                {{ $t('admin.map.is_kiosk') }}
            </label>

            <div class="mt-4 rounded-md bg-slate-50 p-2.5 text-xs text-slate-500">
                <b class="text-slate-600">{{ $t('admin.map.door_label') }}</b>
                {{ doorNode ? $t('admin.map.door_joined') : $t('admin.map.door_no_walkway') }}
                {{ $t('admin.map.door_hint') }}
                <button v-if="!b.doorPick.value" type="button" class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" data-testid="pick-door" @click="b.doorPick.value = b.selectedLocation.value!.id">{{ $t('admin.map.choose_door') }}</button>
                <span v-else class="mt-2 block rounded-md bg-sky-50 p-2 text-sky-700">{{ $t('admin.map.click_door_point') }}
                    <button type="button" class="ml-1 underline rtl:mr-1" @click="b.doorPick.value = null">{{ $t('common.cancel', undefined, 'Cancel') }}</button></span>
            </div>
            <button :class="danger" data-testid="delete" @click="b.remove()">{{ $t('admin.map.delete_place') }}</button>
        </template>

        <!-- ================= a walkway point / lift / stairs ================= -->
        <template v-else-if="b.selectedNode.value">
            <h3 class="text-sm font-semibold">{{ $t('admin.map.node_types.' + b.selectedNode.value.type) || NODE_LABELS[b.selectedNode.value.type] }}</h3>
            <label :class="label">{{ $t('admin.map.kind_of_point') }}</label>
            <select :value="b.selectedNode.value.type" :class="field" data-testid="node-type" @change="b.setNodeType(b.selectedNode.value!.id, val($event) as NodeType)">
                <option v-for="(l, t) in NODE_LABELS" :key="t" :value="t">{{ $t('admin.map.node_types.' + t) || l }}</option>
            </select>
            <p class="mt-2 text-xs text-slate-400">{{ $t('admin.map.connected_to', { count: b.selectedNode.value.connections.length }) }}</p>

            <template v-if="isLift">
                <label :class="label">{{ $t('admin.map.lift_side_label', { type: b.selectedNode.value.type === 'stairs' ? (t('admin.map.node_types.stairs') || 'staircase') : (t('admin.map.node_types.elevator') || 'lift') }) }}</label>
                <select :value="b.liftSide(b.selectedNode.value)" :class="field" data-testid="lift-side" @change="b.setLiftSide(b.selectedNode.value!.id, val($event) as Side)">
                    <option v-for="s in SIDES" :key="s.v" :value="s.v">{{ $t(s.key) || s.l }}</option>
                </select>
                <p class="mt-1 text-xs text-slate-400">{{ $t('admin.map.lift_side_hint') }}</p>

                <div class="mt-4 rounded-md border border-slate-200 p-3">
                    <p class="text-xs font-medium text-slate-600">{{ $t('admin.map.floors_connect') }}</p>
                    <ul class="mt-1.5 space-y-0.5 text-sm">
                        <li v-for="f in b.floors.value" :key="f.id" class="flex items-center gap-2">
                            <span :class="shaftFloors.has(f.id) ? 'text-emerald-600' : 'text-slate-300'">{{ shaftFloors.has(f.id) ? '●' : '○' }}</span>
                            {{ f.name }}
                        </li>
                    </ul>
                    <button class="mt-3 w-full rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900" data-testid="link-shaft" @click="b.linkShaft(b.selectedNode.value!.id)">{{ $t('admin.map.connect_all_floors') }}</button>
                    <button v-if="shaftFloors.size > 1" class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="b.unlinkShaft(b.selectedNode.value!.id)">{{ $t('admin.map.disconnect_floors') }}</button>
                    <p class="mt-2 text-xs text-slate-400">{{ $t('admin.map.missing_floors_hint', { type: b.selectedNode.value.type === 'stairs' ? (t('admin.map.node_types.stairs') || 'staircase') : (t('admin.map.node_types.elevator') || 'lift') }) }}</p>
                </div>
            </template>

            <button :class="danger" data-testid="delete" @click="b.remove()">{{ $t('admin.map.delete_point') }}</button>
        </template>

        <!-- ================= a room / area ================= -->
        <template v-else-if="b.selectedArea.value">
            <h3 class="text-sm font-semibold">{{ $t('admin.map.area_kinds.' + b.selectedArea.value.kind) || AREA_LABELS[b.selectedArea.value.kind] }}</h3>
            <label :class="label">{{ $t('admin.map.inspector_area') }}</label>
            <select :value="b.selectedArea.value.kind" :class="field" data-testid="area-kind" @change="b.updateArea(b.selectedArea.value!.id, { kind: val($event) as AreaKind })">
                <option v-for="(l, k) in AREA_LABELS" :key="k" :value="k">{{ $t('admin.map.area_kinds.' + k) || l }}</option>
            </select>
            <label :class="label">{{ $t('admin.map.area_label') }}</label>
            <input :value="b.selectedArea.value.label ?? ''" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { label: val($event) })" />
            <div class="mt-3 grid grid-cols-2 gap-2">
                <label class="text-xs text-slate-500">X<input type="number" :value="b.selectedArea.value.x" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { x: num($event) })" /></label>
                <label class="text-xs text-slate-500">Y<input type="number" :value="b.selectedArea.value.y" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { y: num($event) })" /></label>
                <label class="text-xs text-slate-500">{{ $t('admin.map.width') }}<input type="number" :value="b.selectedArea.value.w" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { w: num($event) })" /></label>
                <label class="text-xs text-slate-500">{{ $t('admin.map.height') }}<input type="number" :value="b.selectedArea.value.h" :class="field" @change="b.updateArea(b.selectedArea.value!.id, { h: num($event) })" /></label>
            </div>
            <p class="mt-2 text-xs text-slate-400">{{ $t('admin.map.resize_tip') }}</p>
            <button :class="danger" data-testid="delete" @click="b.remove()">{{ $t('admin.map.delete_area') }}</button>
        </template>

        <!-- ================= nothing selected: the floor ================= -->
        <template v-else>
            <h3 class="text-sm font-semibold">{{ $t('admin.map.this_floor') }}</h3>
            <div class="grid grid-cols-[5rem_1fr] gap-2">
                <div><label :class="label">{{ $t('admin.map.short_name') }}</label><input :value="b.floor.value.label" maxlength="4" :class="field" data-testid="floor-label" @change="b.updateFloor(b.floorId.value, { label: val($event) || b.floor.value.label })" /></div>
                <div><label :class="label">{{ $t('admin.map.full_name') }}</label><input :value="b.floor.value.name" :class="field" data-testid="floor-name" @change="b.updateFloor(b.floorId.value, { name: val($event) || b.floor.value.name })" /></div>
            </div>
            <label :class="label">{{ $t('admin.map.level_hint') }}</label>
            <input type="number" :value="b.floor.value.level" :class="field" @change="b.updateFloor(b.floorId.value, { level: num($event) })" />
            <label :class="label">{{ $t('admin.map.plan_image_url') }}</label>
            <input :value="b.floor.value.plan_image ?? ''" placeholder="https://… (drawn under your rooms)" :class="field" @change="b.updateFloor(b.floorId.value, { plan_image: val($event) })" />
            <p class="mt-1 text-xs text-slate-400">{{ $t('admin.map.plan_image_hint') }}</p>

            <div class="mt-5 rounded-md bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">
                <p class="font-medium text-slate-600">{{ $t('admin.map.how_to_build') }}</p>
                <ol class="mt-1 list-decimal space-y-1 pl-4 rtl:pr-4 rtl:pl-0">
                    <li>{{ $t('admin.map.step_1') }}</li>
                    <li>{{ $t('admin.map.step_2') }}</li>
                    <li>{{ $t('admin.map.step_3') }}</li>
                    <li>{{ $t('admin.map.step_4') }}</li>
                    <li>{{ $t('admin.map.step_5') }}</li>
                </ol>
            </div>
        </template>
    </div>
</template>
