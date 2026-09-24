<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  has_map: boolean;
  summary: { floors: number; nodes: number; locations: number; updated_at: string | null } | null;
  json: string;
  errors_list: string[];
  warnings: string[];
  flash_ok: string | null;
}>();

const form = useForm({ json: props.json });
const confirmDemo = ref(false);

const save = () => form.put('/admin/map', { preserveScroll: true });

function loadDemo() {
  router.post('/admin/map/demo', {}, { preserveScroll: true, onFinish: () => (confirmDemo.value = false) });
}
</script>

<template>
  <div class="max-w-5xl">
    <h1 class="text-xl font-semibold text-slate-800">{{ $t('admin.map.title', 'Hotel Map') }}</h1>
    <p class="mt-1 mb-4 text-sm text-slate-500">
      {{ $t('admin.map.subtitle', 'The interactive map guests see at /map: floors, walkways and places. It is stored as one JSON document that is checked before saving. This is the advanced view of the same data the visual builder edits.') }}
    </p>

    <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
      {{ $t('admin.map.visual_notice', 'Want to edit visually?') }} <a href="/admin/map/builder" class="font-medium underline">{{ $t('admin.map.open_builder', 'Open the map builder') }}</a> — {{ $t('admin.map.visual_desc', 'draw rooms and walkways, place restaurants, connect lifts.') }}
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white p-4">
      <template v-if="summary">
        <span class="text-sm text-slate-700">
          <b>{{ summary.floors }}</b> {{ $t('admin.map.floors', 'floors') }} ·
          <b>{{ summary.nodes }}</b> {{ $t('admin.map.walkway_points', 'walkway points') }} ·
          <b>{{ summary.locations }}</b> {{ $t('admin.map.places', 'places') }}
        </span>
        <span v-if="summary.updated_at" class="text-xs text-slate-400">{{ $t('admin.map.updated', 'updated') }} {{ summary.updated_at }}</span>
        <a href="/map" target="_blank" class="text-sm text-sky-700 underline">{{ $t('admin.map.open_guest_map', 'Open guest map') }}</a>
      </template>
      <span v-else class="text-sm text-slate-500">{{ $t('admin.map.empty', 'No map yet — guests see a "map isn\'t ready" message.') }}</span>

      <div class="ms-auto">
        <button v-if="!confirmDemo" type="button" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" data-testid="demo-btn" @click="confirmDemo = true">
          {{ $t('admin.map.load_demo', 'Load demo hotel map') }}
        </button>
        <span v-else class="flex items-center gap-2 text-sm">
          <span class="text-slate-600">{{ has_map ? $t('admin.map.replace_warning', 'This replaces your current map.') : $t('admin.map.load_confirm', 'Load the demo map?') }}</span>
          <button type="button" class="rounded-md bg-slate-800 px-3 py-1.5 text-white" data-testid="demo-confirm" @click="loadDemo">{{ $t('admin.map.confirm_load', 'Yes, load it') }}</button>
          <button type="button" class="rounded-md border border-slate-300 px-3 py-1.5" @click="confirmDemo = false">{{ $t('common.cancel', 'Cancel') }}</button>
        </span>
      </div>
    </div>

    <p v-if="flash_ok" class="mb-3 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-700" data-testid="saved">{{ flash_ok }}</p>

    <div v-if="errors_list.length" class="mb-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700" data-testid="errors">
      <p class="font-medium">{{ $t('admin.map.not_saved', 'The map was not saved:') }}</p>
      <ul class="mt-1 list-disc ps-5"><li v-for="e in errors_list" :key="e">{{ e }}</li></ul>
    </div>
    <div v-if="warnings.length" class="mb-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800" data-testid="warnings">
      <p class="font-medium">{{ $t('admin.map.warnings_title', 'Saved, but please check:') }}</p>
      <ul class="mt-1 list-disc ps-5"><li v-for="w in warnings" :key="w">{{ w }}</li></ul>
    </div>

    <form class="rounded-lg border border-slate-200 bg-white p-4" @submit.prevent="save">
      <label class="mb-1 block text-sm font-medium text-slate-700" for="map-json">{{ $t('admin.map.json_label', 'Map data (JSON)') }}</label>
      <textarea id="map-json" v-model="form.json" spellcheck="false" rows="22" class="w-full rounded-md border border-slate-300 p-3 font-mono text-xs leading-relaxed" placeholder='{ "version": 1, "meters_per_unit": 0.1, "floors": [...], "nodes": [...], "locations": [...] }' />
      <p v-if="form.errors.json" class="mt-1 text-xs text-red-600">{{ form.errors.json }}</p>
      <div class="mt-3 flex items-center justify-end gap-3">
        <button type="submit" :disabled="form.processing || !form.json.trim()" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50" data-testid="save">
          {{ form.processing ? $t('admin.common.saving', 'Saving…') : $t('admin.map.validate_save', 'Validate & save') }}
        </button>
      </div>
    </form>
  </div>
</template>
