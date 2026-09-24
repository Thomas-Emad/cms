<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BuilderCanvas from '@/Map/builder/BuilderCanvas.vue';
import BuilderInspector, { type ContentOption } from '@/Map/builder/BuilderInspector.vue';
import { type Issue, type Tool, useMapBuilder } from '@/Map/builder/useMapBuilder';
import { useCamera } from '@/Map/camera';
import { AREA_STYLE, CATEGORY_META } from '@/Map/categories';
import { formatDistance, formatDuration } from '@/Map/routing';
import type { AreaKind, HotelMapData, LocationCategory, NodeType, Route } from '@/Map/types';
import { Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from '@/i18n';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  map: HotelMapData | null;
  content: ContentOption[];
  errors_list: string[];
  warnings: string[];
  flash_ok: string | null;
}>();

const { t } = useI18n();
const b = useMapBuilder(props.map);
const camera = useCamera();
const saving = ref(false);
const bottom = ref<'checks' | 'route'>('checks');

/* ------------------------------ camera ------------------------------ */
function fitFloor(immediate = true) {
  const f = b.floor.value;
  camera.fitHome({ x: 0, y: 0, w: f.width, h: f.height }, 30, immediate);
}
let inited = false;
watch(() => camera.width.value, (w) => { if (w > 0 && !inited) { inited = true; fitFloor(); } }, { immediate: true });
watch(() => b.floorId.value, () => inited && fitFloor(false));

/* ------------------------------ tools ------------------------------ */
const TOOLS: { id: Tool; label: string; icon: string; key: string; hint: string }[] = [
  { id: 'select', label: 'Select', icon: '↖', key: 'V', hint: 'Click something to edit it. Drag to move it. Drag empty space to pan, scroll to zoom.' },
  { id: 'area', label: 'Area', icon: '▭', key: 'A', hint: 'Drag on the plan to draw a room, hall or outline.' },
  { id: 'path', label: 'Walkway', icon: '⌇', key: 'W', hint: 'Click along the corridors to lay walkway points. Click an existing point to join or continue from it. Press Esc when done.' },
  { id: 'location', label: 'Place', icon: '📍', key: 'P', hint: 'Click inside a room to add a place (restaurant, room, spa…). Then name it on the right.' },
];
const hint = computed(() => (b.doorPick.value ? t('admin.map.click_door_point', undefined, 'Click the walkway point that is this place\'s door.') : t(`admin.map.tool_hints.${b.tool.value}`, undefined, TOOLS.find((t) => t.id === b.tool.value)!.hint)));
function setTool(t: Tool) {
  b.tool.value = t;
  b.pathFrom.value = null;
  b.doorPick.value = null;
}

/* ------------------------------ floors ------------------------------ */
const adding = ref(false);
const newFloor = ref({ label: '', name: '', level: 0, copyFrom: '' as string });
function openAdd() {
  const top = b.floors.value[b.floors.value.length - 1];
  newFloor.value = { label: String((top?.level ?? -1) + 1), name: '', level: (top?.level ?? -1) + 1, copyFrom: b.floorId.value };
  adding.value = true;
}
function addFloor() {
  const f = newFloor.value;
  if (!f.label.trim()) return;
  b.addFloor({ label: f.label.trim(), name: f.name.trim() || `Floor ${f.label.trim()}`, level: Number(f.level), copyFrom: f.copyFrom || null });
  adding.value = false;
}
function removeFloor() {
  if (b.floors.value.length <= 1) return;
  if (confirm(`${t('admin.map.delete_floor_confirm', 'Delete')} "${b.floor.value.name}" ${t('admin.map.delete_floor_details', 'and everything on it (rooms, walkways, places)? You can undo this.')}`)) b.deleteFloor(b.floorId.value);
}
const floorsTopDown = computed(() => [...b.floors.value].reverse());

/* ------------------------------ checks & test route ------------------------------ */
/** Links that would silently not work for guests (draft / deleted / badly typed). */
const linkIssues = computed(() => {
  const out: Issue[] = [];
  for (const l of b.data.value.locations) {
    const target = { kind: 'location' as const, id: l.id, floor: l.floor };
    if (l.link && !/^(\/(?!\/)|https?:\/\/)/i.test(l.link)) out.push({ level: 'error', text: t('admin.map.issues.link_bad_url', { name: l.name }, `${l.name}: the page address must start with / or https://`), target });
    if (l.ref && !l.link) {
      const c = props.content.find((x) => x.type === l.ref!.type && x.slug === l.ref!.slug);
      if (!c) out.push({ level: 'warning', text: t('admin.map.issues.link_missing_page', { name: l.name }, `${l.name} is linked to a page that no longer exists: guests won't get "View Details".`), target });
      else if (!c.published) out.push({ level: 'warning', text: t('admin.map.issues.link_unpublished_page', { name: l.name, page: c.name }, `${l.name} is linked to "${c.name}", which isn't published: guests won't get "View Details" until you publish it.`), target });
    }
  }
  return out;
});
const allIssues = computed<Issue[]>(() => [...b.issues.value, ...linkIssues.value]);
const errors = computed(() => allIssues.value.filter((i) => i.level === 'error'));
function jump(t?: { kind: 'area' | 'node' | 'location'; id: string; floor: string }) {
  if (!t) return;
  b.floorId.value = t.floor;
  b.tool.value = 'select';
  b.selection.value = { kind: t.kind, id: t.id };
}
const from = ref('');
const to = ref('');
const route = ref<Route | null>(null);
const routeTried = ref(false);
const places = computed(() => [...b.data.value.locations].sort((a, c) => (b.floorOf(a.floor)?.level ?? 0) - (b.floorOf(c.floor)?.level ?? 0) || a.name.localeCompare(c.name)));
function runTest() {
  routeTried.value = true;
  route.value = b.testRoute(from.value, to.value);
  const first = route.value?.segments[0];
  if (first) b.floorId.value = first.floor;
}
watch(() => [b.data.value.locations.length, b.data.value.nodes.length], () => (route.value = null));

/* ------------------------------ saving ------------------------------ */
function save() {
  if (saving.value || errors.value.length) return;
  saving.value = true;
  router.put('/admin/map/save', { data: b.toJSON() } as never, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => { if ((page.props as { flash_ok?: string }).flash_ok) b.markSaved(); },
    onFinish: () => (saving.value = false),
  });
}

/* ------------------------------ keyboard & leaving ------------------------------ */
function onKey(e: KeyboardEvent) {
  const t = e.target as HTMLElement;
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes(t.tagName)) return;
  const mod = e.ctrlKey || e.metaKey;
  if (mod && e.key.toLowerCase() === 'z') { e.preventDefault(); e.shiftKey ? b.redo() : b.undo(); return; }
  if (mod && e.key.toLowerCase() === 'y') { e.preventDefault(); b.redo(); return; }
  if (mod && e.key.toLowerCase() === 's') { e.preventDefault(); save(); return; }
  if (e.key === 'Delete' || e.key === 'Backspace') { if (b.selection.value) { e.preventDefault(); b.remove(); } return; }
  if (e.key === 'Escape') {
    if (b.pathFrom.value || b.doorPick.value) { b.pathFrom.value = null; b.doorPick.value = null; } else if (b.selection.value) b.selection.value = null; else setTool('select');
    return;
  }
  if (!mod) {
    const tool = TOOLS.find((x) => x.key.toLowerCase() === e.key.toLowerCase());
    if (tool) setTool(tool.id);
  }
}
const beforeUnload = (e: BeforeUnloadEvent) => { if (b.dirty.value) { e.preventDefault(); e.returnValue = ''; } };
let offRouter: (() => void) | undefined;
onMounted(() => {
  window.addEventListener('keydown', onKey);
  window.addEventListener('beforeunload', beforeUnload);
  offRouter = router.on('before', (event) => {
    if (event.detail.visit.method === 'get' && b.dirty.value && !confirm(t('admin.map.unsaved_leave_confirm', 'You have unsaved changes to the map. Leave without saving?'))) event.preventDefault();
  });
});
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKey);
  window.removeEventListener('beforeunload', beforeUnload);
  offRouter?.();
});

const btn = 'rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-700 hover:bg-slate-50 disabled:opacity-40';
const sel = 'w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm';
</script>

<template>
  <div class="flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white" style="height: calc(100vh - 6.5rem)" data-testid="builder">
    <!-- ============ top bar ============ -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 px-3 py-2">
      <h1 class="mr-2 text-base font-semibold text-slate-800">{{ $t('admin.map.builder_title') }}</h1>
      <button :class="btn" :disabled="!b.canUndo.value" title="Undo (Ctrl+Z)" data-testid="undo" @click="b.undo()">{{ $t('admin.map.undo') }}</button>
      <button :class="btn" :disabled="!b.canRedo.value" title="Redo (Ctrl+Shift+Z)" data-testid="redo" @click="b.redo()">{{ $t('admin.map.redo') }}</button>
      <span class="mx-1 h-5 w-px bg-slate-200" />
      <label class="flex items-center gap-1.5 text-sm text-slate-600"><input v-model="b.snapOn.value" type="checkbox" /> {{ $t('admin.map.snap_to_grid') }}</label>
      <label class="flex items-center gap-1.5 text-sm text-slate-600"><input v-model="b.ortho.value" type="checkbox" /> {{ $t('admin.map.straight_walkways') }}</label>
      <span class="mx-1 h-5 w-px bg-slate-200" />
      <button :class="btn" :aria-label="$t('map.zoom_in')" @click="camera.zoomBy(1.4)">＋</button>
      <button :class="btn" :aria-label="$t('map.zoom_out')" @click="camera.zoomBy(1 / 1.4)">－</button>
      <button :class="btn" @click="fitFloor(false)">{{ $t('admin.map.fit') }}</button>

      <div class="ml-auto flex items-center gap-3">
        <span v-if="flash_ok && !b.dirty.value" class="text-sm text-emerald-600" data-testid="saved">✓ {{ flash_ok }}</span>
        <span v-else-if="b.dirty.value" class="text-sm text-amber-600" data-testid="dirty">{{ $t('admin.map.unsaved_changes') }}</span>
        <a href="/map" target="_blank" class="text-sm text-sky-700 underline">{{ $t('admin.map.guest_view') }}</a>
        <Link href="/admin/map" class="text-sm text-slate-400 underline">{{ $t('admin.map.advanced_json') }}</Link>
        <button
          class="rounded-md bg-slate-800 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-40"
          :disabled="saving || !b.dirty.value || errors.length > 0"
          :title="errors.length ? 'Fix the problems listed below first' : 'Save (Ctrl+S)'"
          data-testid="save"
          @click="save"
        >
          {{ saving ? $t('common.saving') : $t('admin.map.save_map') }}
        </button>
      </div>
    </div>

    <!-- messages from the server after a save -->
    <div v-if="errors_list.length" class="border-b border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700" data-testid="server-errors">
      <b>{{ $t('admin.map.not_saved') }}</b> <span v-for="(e, i) in errors_list" :key="i">{{ e }} </span>
    </div>
    <div v-else-if="warnings.length && !b.dirty.value" class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">
      <b>{{ $t('admin.map.warnings_title') }}</b> <span v-for="(w, i) in warnings" :key="i">{{ w }} </span>
    </div>

    <div class="flex min-h-0 flex-1">
      <!-- ============ left: floors + tools ============ -->
      <aside class="w-56 shrink-0 overflow-y-auto border-r border-slate-200 p-3">
        <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $t('admin.map.tools') }}</p>
        <div class="grid grid-cols-2 gap-1.5">
          <button
            v-for="t in TOOLS"
            :key="t.id"
            class="flex flex-col items-center rounded-md border px-2 py-2 text-xs transition"
            :class="b.tool.value === t.id ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
            :title="`${$t('admin.map.tool_' + t.id) || t.label} (${t.key})`"
            :data-testid="`tool-${t.id}`"
            @click="setTool(t.id)"
          >
            <span class="text-lg leading-none">{{ t.icon }}</span>{{ $t('admin.map.tool_' + t.id) || t.label }}
          </button>
        </div>

        <div v-if="b.tool.value === 'area'" class="mt-3">
          <label class="mb-1 block text-xs text-slate-500">{{ $t('admin.map.drawing_a') }}</label>
          <select v-model="b.areaKind.value" :class="sel" data-testid="area-kind-pick">
            <option v-for="k in (Object.keys(AREA_STYLE) as AreaKind[])" :key="k" :value="k">{{ $t('admin.map.area_kinds.' + k) }}</option>
          </select>
        </div>
        <div v-if="b.tool.value === 'location'" class="mt-3">
          <label class="mb-1 block text-xs text-slate-500">{{ $t('admin.map.adding_a') }}</label>
          <select v-model="b.placeCategory.value" :class="sel">
            <option v-for="(m, c) in CATEGORY_META" :key="c" :value="(c as LocationCategory)">{{ m.icon }} {{ $t('map.categories.' + c) || m.label }}</option>
          </select>
        </div>
        <div v-if="b.tool.value === 'path'" class="mt-3">
          <label class="mb-1 block text-xs text-slate-500">{{ $t('admin.map.new_points_are') }}</label>
          <select v-model="b.pathNodeType.value" :class="sel">
            <option v-for="(l, t) in ({ walk: 'walk_plural', entrance: 'entrance_plural', elevator: 'elevator_plural', stairs: 'stairs' } as Record<NodeType, string>)" :key="t" :value="t">{{ $t('admin.map.node_types.' + l) }}</option>
          </select>
          <button v-if="b.pathFrom.value" :class="[btn, 'mt-2 w-full']" data-testid="path-done" @click="b.pathFrom.value = null">{{ $t('admin.map.finish_walkway') }}</button>
        </div>

        <p class="mb-1.5 mt-5 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $t('admin.map.floors_title') }}</p>
        <ul class="space-y-1" data-testid="floor-list">
          <li v-for="f in floorsTopDown" :key="f.id">
            <button
              class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm"
              :class="f.id === b.floorId.value ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
              :data-testid="`floor-${f.label}`"
              @click="b.floorId.value = f.id; b.selection.value = null"
            >
              <span class="flex h-6 w-8 items-center justify-center rounded bg-slate-800 text-xs text-white">{{ f.label }}</span>
              <span class="truncate">{{ f.name }}</span>
            </button>
          </li>
        </ul>

        <button v-if="!adding" :class="[btn, 'mt-2 w-full']" data-testid="add-floor" @click="openAdd">{{ $t('admin.map.add_floor') }}</button>
        <form v-else class="mt-2 space-y-2 rounded-md border border-slate-200 p-2" @submit.prevent="addFloor">
          <div class="grid grid-cols-[3.5rem_1fr] gap-2">
            <input v-model="newFloor.label" :placeholder="$t('admin.map.short_placeholder')" maxlength="4" :class="sel" data-testid="nf-label" />
            <input v-model="newFloor.name" :placeholder="$t('admin.map.full_name_placeholder')" :class="sel" />
          </div>
          <input v-model.number="newFloor.level" type="number" :class="sel" :title="$t('admin.map.level_title')" />
          <select v-model="newFloor.copyFrom" :class="sel" data-testid="nf-copy">
            <option value="">{{ $t('admin.map.start_empty') }}</option>
            <option v-for="f in floorsTopDown" :key="f.id" :value="f.id">{{ $t('admin.map.copy_layout_of', { name: f.name }) }}</option>
          </select>
          <p class="text-xs text-slate-400">{{ $t('admin.map.copy_layout_hint') }}</p>
          <div class="flex gap-2"><button type="submit" class="flex-1 rounded-md bg-slate-800 px-2 py-1.5 text-sm text-white" data-testid="nf-submit">{{ $t('common.add') }}</button><button type="button" :class="btn" @click="adding = false">{{ $t('common.cancel') }}</button></div>
        </form>
        <button v-if="b.floors.value.length > 1" class="mt-2 w-full text-left text-xs text-red-500 hover:underline" data-testid="delete-floor" @click="removeFloor">{{ $t('admin.map.delete_floor') }}</button>
      </aside>

      <!-- ============ centre: canvas ============ -->
      <div class="relative min-w-0 flex-1">
        <BuilderCanvas :b="b" :camera="camera" :route="route" />
        <div class="pointer-events-none absolute left-3 right-3 top-3 flex justify-center">
          <p class="pointer-events-auto max-w-xl rounded-full bg-white/90 px-4 py-1.5 text-center text-sm text-slate-700 shadow" data-testid="hint">{{ hint }}</p>
        </div>
        <p class="pointer-events-none absolute bottom-2 left-3 rounded bg-white/80 px-2 py-0.5 text-xs text-slate-500">{{ b.floor.value.name }}</p>
      </div>

      <!-- ============ right: inspector ============ -->
      <aside class="w-72 shrink-0 overflow-y-auto border-l border-slate-200 p-4">
        <BuilderInspector :b="b" :content="content" />
      </aside>
    </div>

    <!-- ============ bottom: checks + test route ============ -->
    <div class="border-t border-slate-200">
      <div class="flex gap-4 px-3 pt-1.5 text-sm">
        <button :class="bottom === 'checks' ? 'border-b-2 border-slate-800 font-medium text-slate-900' : 'text-slate-500'" data-testid="tab-checks" @click="bottom = 'checks'">
          {{ $t('admin.map.checks') }} <span v-if="allIssues.length" class="ml-1 rounded-full px-1.5 text-xs text-white" :class="errors.length ? 'bg-red-500' : 'bg-amber-500'">{{ allIssues.length }}</span><span v-else class="ml-1 text-emerald-600">✓</span>
        </button>
        <button :class="bottom === 'route' ? 'border-b-2 border-slate-800 font-medium text-slate-900' : 'text-slate-500'" data-testid="tab-route" @click="bottom = 'route'">{{ $t('admin.map.test_route') }}</button>
      </div>

      <div class="max-h-40 overflow-y-auto px-3 pb-2 pt-1.5 text-sm">
        <template v-if="bottom === 'checks'">
          <p v-if="!allIssues.length" class="py-1 text-emerald-700" data-testid="all-good">{{ $t('admin.map.all_good') }}</p>
          <ul v-else class="space-y-0.5" data-testid="issues">
            <li v-for="(i, n) in allIssues" :key="n">
              <button class="flex w-full items-start gap-2 rounded px-1 py-0.5 text-left hover:bg-slate-50" @click="jump(i.target)">
                <span :class="i.level === 'error' ? 'text-red-500' : 'text-amber-500'">●</span>
                <span :class="i.level === 'error' ? 'text-red-700' : 'text-slate-600'">{{ i.text }}</span>
              </button>
            </li>
          </ul>
        </template>

        <template v-else>
          <div class="flex flex-wrap items-center gap-2">
            <select v-model="from" :class="[sel, 'w-56']" data-testid="route-from"><option value="">{{ $t('admin.map.from_placeholder') }}</option><option v-for="l in places" :key="l.id" :value="l.id">{{ l.name }} ({{ b.floorOf(l.floor)?.label }})</option></select>
            <span class="text-slate-400">→</span>
            <select v-model="to" :class="[sel, 'w-56']" data-testid="route-to"><option value="">{{ $t('admin.map.to_placeholder') }}</option><option v-for="l in places" :key="l.id" :value="l.id">{{ l.name }} ({{ b.floorOf(l.floor)?.label }})</option></select>
            <button :class="btn" :disabled="!from || !to || from === to" data-testid="route-go" @click="runTest">{{ $t('admin.map.show_route') }}</button>
          </div>
          <p v-if="routeTried && !route" class="mt-2 text-red-600" data-testid="route-none">{{ $t('admin.map.no_walking_route') }}</p>
          <div v-if="route" class="mt-2" data-testid="route-result">
            <p class="font-medium text-slate-800">{{ formatDistance(route.meters) || '0 m' }} · {{ formatDuration(route.seconds) }} <span class="font-normal text-slate-400">{{ $t('admin.map.green_line_hint') }}</span></p>
            <ol class="mt-1 list-decimal pl-5 text-slate-600"><li v-for="s in route.steps" :key="s.index">{{ s.detail ?? s.text }}</li></ol>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
