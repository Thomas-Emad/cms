<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

interface Entry {
  id: number | null;
  group: string | null;
  label: string;
  value: string;
}

const props = defineProps<{
  title: string;
  description: string;
  has_group: boolean;
  group_placeholder: string;
  label_placeholder: string;
  value_placeholder: string;
  save_url: string;
  entries: Entry[];
}>();

const form = useForm({
  entries: props.entries.map((e) => ({ ...e })) as Entry[],
});

// Suggest groups already used, so "Pool" isn't typed three different ways.
const groups = computed(() => [...new Set(form.entries.map((e) => e.group).filter(Boolean))] as string[]);

function add() {
  const last = form.entries[form.entries.length - 1];
  form.entries.push({ id: null, group: last?.group ?? '', label: '', value: '' });
}
function remove(i: number) {
  form.entries.splice(i, 1);
}
function move(i: number, dir: -1 | 1) {
  const j = i + dir;
  if (j < 0 || j >= form.entries.length) return;
  [form.entries[i], form.entries[j]] = [form.entries[j], form.entries[i]];
}

// Ignore fully blank rows (added by accident) instead of failing validation.
const submit = () =>
  form
    .transform((d) => ({ entries: d.entries.filter((e) => e.label.trim() || e.value.trim()) }))
    .put(props.save_url, { preserveScroll: true });

const input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm';
</script>

<template>
  <div class="max-w-4xl">
    <h1 class="text-xl font-semibold text-slate-800">{{ title }}</h1>
    <p class="mt-1 mb-4 text-sm text-slate-500">{{ description }}</p>

    <form @submit.prevent="submit" class="rounded-lg border border-slate-200 bg-white p-4">
      <datalist id="known-groups"><option v-for="g in groups" :key="g" :value="g" /></datalist>

      <div v-if="form.entries.length" class="space-y-2">
        <div v-for="(entry, i) in form.entries" :key="entry.id ?? `new-${i}`" class="flex items-start gap-2">
          <input v-if="has_group" v-model="entry.group" list="known-groups" type="text" :placeholder="group_placeholder" :class="[input, 'w-40']" />
          <div class="flex-1">
            <input v-model="entry.label" type="text" :placeholder="label_placeholder" :class="input" />
            <p v-if="form.errors[`entries.${i}.label`]" class="mt-1 text-xs text-red-600">{{ form.errors[`entries.${i}.label`] }}</p>
          </div>
          <div class="w-56">
            <input v-model="entry.value" type="text" :placeholder="value_placeholder" :class="input" />
            <p v-if="form.errors[`entries.${i}.value`]" class="mt-1 text-xs text-red-600">{{ form.errors[`entries.${i}.value`] }}</p>
          </div>
          <div class="flex gap-1 pt-1">
            <button type="button" class="h-8 w-8 rounded border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Move up" @click="move(i, -1)">↑</button>
            <button type="button" class="h-8 w-8 rounded border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Move down" @click="move(i, 1)">↓</button>
            <button type="button" class="h-8 w-8 rounded border border-slate-200 text-red-500 hover:bg-red-50" aria-label="Remove row" @click="remove(i)">×</button>
          </div>
        </div>
      </div>
      <p v-else class="py-6 text-center text-sm text-slate-400">Nothing here yet — add your first row.</p>

      <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
        <button type="button" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" @click="add">+ Add row</button>
        <div class="flex items-center gap-3">
          <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Saved</span>
          <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
            Save
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
