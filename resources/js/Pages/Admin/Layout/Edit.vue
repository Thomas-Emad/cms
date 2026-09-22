<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { TEMPLATES, TILE_DIMENSIONS, type GuestLayoutConfig, type MenuItem, type TileSize } from '@/Layouts/guest/shellConfig';
import ClassicShell from '@/Layouts/guest/ClassicShell.vue';
import TvShell from '@/Layouts/guest/TvShell.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  config: GuestLayoutConfig;
  defaults: GuestLayoutConfig;
  errors_list: string[];
  flash_ok: string | null;
}>();

const form = useForm<GuestLayoutConfig>({ ...props.config, items: props.config.items.map((i) => ({ ...i })) });

function addItem() {
  form.items.push({ href: '/', label: 'New item', icon: '⭐', color: null, visible: true });
}
function removeItem(i: number) {
  form.items.splice(i, 1);
}
function move(i: number, dir: -1 | 1) {
  const j = i + dir;
  if (j < 0 || j >= form.items.length) return;
  [form.items[i], form.items[j]] = [form.items[j], form.items[i]];
}
function resetItems() {
  if (confirm('Replace the menu with the default items?')) form.items = props.defaults.items.map((i) => ({ ...i }));
}

const save = () => form.put('/admin/layout', { preserveScroll: true });

// A safe, real Hotel object for the live preview (not the actual current hotel).
const previewHotel = { name: 'Grand Horizon', slug: 'preview' } as any;
const previewTab = ref<'home' | 'other'>('home');
const previewConfig = computed<GuestLayoutConfig>(() => form.data());

const field = 'w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm';
const label = 'mb-1 mt-3 block text-xs font-medium text-slate-500';
</script>

<template>
  <div class="grid max-w-7xl grid-cols-1 gap-6 xl:grid-cols-[26rem_1fr]">
    <!-- ============ form ============ -->
    <form class="min-w-0" @submit.prevent="save">
      <h1 class="text-xl font-semibold text-slate-800">Guest Layout</h1>
      <p class="mt-1 text-sm text-slate-500">Choose how the guest screens look and what's in the menu. Changes apply to every guest screen as soon as you save.</p>

      <p v-if="flash_ok && !form.isDirty" class="mt-3 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-700" data-testid="saved">{{ flash_ok }}</p>
      <div v-if="errors_list.length" class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700" data-testid="errors">
        <p class="font-medium">Not saved:</p>
        <ul class="mt-1 list-disc pl-5"><li v-for="e in errors_list" :key="e">{{ e }}</li></ul>
      </div>

      <!-- template picker -->
      <div class="mt-4 grid grid-cols-2 gap-3">
        <button
          v-for="t in TEMPLATES"
          :key="t.id"
          type="button"
          class="rounded-lg border p-3 text-left transition"
          :class="form.template === t.id ? 'border-slate-800 ring-2 ring-slate-800' : 'border-slate-200 hover:bg-slate-50'"
          :data-testid="`template-${t.id}`"
          @click="form.template = t.id"
        >
          <span class="block text-sm font-semibold text-slate-800">{{ t.name }}</span>
          <span class="mt-0.5 block text-xs text-slate-500">{{ t.description }}</span>
        </button>
      </div>

      <div class="mt-2 rounded-lg border border-slate-200 bg-white p-4">
        <label class="flex items-center justify-between text-sm">Show the clock <input v-model="form.show_clock" type="checkbox" /></label>

        <template v-if="form.template === 'tv'">
          <label class="mt-3 flex items-center justify-between text-sm">
            Lock the home screen (no scrolling)
            <input v-model="form.lock_home_scroll" type="checkbox" data-testid="lock-scroll" />
          </label>
          <label :class="label">Tile size</label>
          <select v-model="form.tile_size" :class="field" data-testid="tile-size">
            <option v-for="s in (Object.keys(TILE_DIMENSIONS) as TileSize[])" :key="s" :value="s">{{ s[0].toUpperCase() + s.slice(1) }}</option>
          </select>
          <label :class="label">Headline over the home picture (optional)</label>
          <input v-model="form.headline" maxlength="80" placeholder="e.g. Welcome to Grand Horizon" :class="field" />
          <label :class="label">Tagline under the hotel name (optional)</label>
          <input v-model="form.tagline" maxlength="80" placeholder="e.g. A Smarter Stay" :class="field" />
        </template>
      </div>

      <!-- menu items -->
      <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between">
          <p class="text-sm font-semibold text-slate-700">Menu items</p>
          <button type="button" class="text-xs text-slate-400 hover:underline" @click="resetItems">Reset to defaults</button>
        </div>

        <ul class="mt-2 space-y-2" data-testid="items">
          <li v-for="(it, i) in form.items" :key="i" class="rounded-md border border-slate-200 p-2.5" :class="{ 'opacity-50': !it.visible }">
            <div class="flex items-center gap-2">
              <div class="flex flex-col">
                <button type="button" class="text-slate-400 hover:text-slate-700 disabled:opacity-20" :disabled="i === 0" aria-label="Move up" @click="move(i, -1)">▲</button>
                <button type="button" class="text-slate-400 hover:text-slate-700 disabled:opacity-20" :disabled="i === form.items.length - 1" aria-label="Move down" @click="move(i, 1)">▼</button>
              </div>
              <input v-model="it.icon" maxlength="4" class="w-11 rounded-md border border-slate-300 px-1 py-1.5 text-center text-lg" :aria-label="`Icon for ${it.label}`" />
              <input v-model="it.label" class="min-w-0 flex-1 rounded-md border border-slate-300 px-2 py-1.5 text-sm" :aria-label="`Name for item ${i + 1}`" />
              <input v-if="form.template === 'tv'" type="color" :value="it.color ?? '#1f2937'" class="h-8 w-8 shrink-0 rounded" title="Tile colour" @input="it.color = ($event.target as HTMLInputElement).value" />
              <label class="flex shrink-0 items-center gap-1 text-xs text-slate-500"><input v-model="it.visible" type="checkbox" />Show</label>
              <button type="button" class="shrink-0 text-red-400 hover:text-red-600" aria-label="Delete item" @click="removeItem(i)">✕</button>
            </div>
            <input v-model="it.href" placeholder="/facilities" class="mt-1.5 w-full rounded-md border border-slate-300 px-2 py-1 font-mono text-xs" :aria-label="`Page address for ${it.label}`" />
          </li>
        </ul>
        <button type="button" class="mt-2 w-full rounded-md border border-dashed border-slate-300 py-1.5 text-sm text-slate-500 hover:bg-slate-50" data-testid="add-item" @click="addItem">+ Add item</button>
      </div>

      <button type="submit" :disabled="form.processing" class="mt-4 w-full rounded-md bg-slate-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50" data-testid="save">
        {{ form.processing ? 'Saving…' : 'Save layout' }}
      </button>
    </form>

    <!-- ============ live preview ============ -->
    <div class="min-w-0">
      <div class="mb-2 flex gap-2">
        <button type="button" class="rounded-md border px-3 py-1 text-sm" :class="previewTab === 'home' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600'" data-testid="preview-home" @click="previewTab = 'home'">Home screen</button>
        <button type="button" class="rounded-md border px-3 py-1 text-sm" :class="previewTab === 'other' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600'" data-testid="preview-other" @click="previewTab = 'other'">Another page</button>
      </div>
      <div class="overflow-hidden rounded-xl border border-slate-300 bg-slate-900" style="aspect-ratio: 16 / 9" data-testid="preview">
        <div class="h-full w-full origin-top-left" style="width: 177.78%; height: 177.78%; transform: scale(0.5625); font-size: 22px">
          <div :key="previewTab" style="height: 100%">
            <component :is="previewConfig.template === 'tv' ? TvShell : ClassicShell" :hotel="previewHotel" :config="previewConfig" :preview="true" :data-preview-page="previewTab">
              <div v-if="previewTab === 'home' && previewConfig.template === 'tv'" class="h-full w-full bg-gradient-to-br from-slate-700 to-slate-900" />
              <div v-else class="min-h-[140%] bg-white p-10 pt-28 text-slate-400">— page content —<div class="mt-4 h-96 rounded-lg bg-slate-100" /></div>
            </component>
          </div>
        </div>
      </div>
      <p class="mt-2 text-xs text-slate-400">Preview only — it doesn't scroll or respond to the remote here. Save and open a guest screen to try it for real.</p>
    </div>
  </div>
</template>
