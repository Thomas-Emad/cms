<script setup lang="ts">
import { inject, computed } from 'vue';
import { SECTION_REGISTRY } from './registry';
import type { PageBuilderStore } from './store';

const store = inject<PageBuilderStore>('pageBuilderStore')!;

const section = computed(() => store.selectedSection.value);
const entry = computed(() => (section.value ? SECTION_REGISTRY[section.value.type] : null));

/**
 * text/textarea/number fields are typed character-by-character, so they
 * go through the debounced/batched history path (one undo step per
 * "pause in typing", not one per keystroke). toggle/select/media fields
 * are already atomic, discrete actions - each one is immediately a
 * meaningful change, so they use the immediate-history path.
 */
function onTypedFieldInput(key: string, value: unknown) {
  if (!section.value) return;
  store.updateSectionPropsBatched(section.value.id, { [key]: value }, `${section.value.id}:${key}`);
}

function onDiscreteFieldInput(key: string, value: unknown) {
  if (!section.value) return;
  store.updateSectionProps(section.value.id, { [key]: value });
}
</script>

<template>
  <div class="w-72 shrink-0 border-l border-slate-200 bg-white p-4 overflow-y-auto">
    <div v-if="!section" class="text-sm text-slate-400">
      Select a section to edit its properties.
    </div>

    <div v-else>
      <h2 class="text-sm font-semibold text-slate-800 mb-3">{{ entry?.label ?? section.type }}</h2>

      <div v-for="field in entry?.editorFields ?? []" :key="field.key" class="mb-3">
        <label class="block text-xs font-medium text-slate-600 mb-1">{{ field.label }}</label>

        <input
          v-if="field.type === 'text' || field.type === 'url'"
          :value="section.props[field.key] ?? ''"
          type="text"
          class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
          @input="onTypedFieldInput(field.key, ($event.target as HTMLInputElement).value)"
        />

        <textarea
          v-else-if="field.type === 'textarea'"
          :value="section.props[field.key] ?? ''"
          rows="3"
          class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
          @input="onTypedFieldInput(field.key, ($event.target as HTMLTextAreaElement).value)"
        />

        <input
          v-else-if="field.type === 'number'"
          :value="section.props[field.key] ?? ''"
          type="number"
          :min="field.min"
          :max="field.max"
          class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
          @input="onTypedFieldInput(field.key, Number(($event.target as HTMLInputElement).value))"
        />

        <label v-else-if="field.type === 'toggle'" class="flex items-center gap-2">
          <input
            :checked="!!section.props[field.key]"
            type="checkbox"
            class="rounded border-slate-300"
            @change="onDiscreteFieldInput(field.key, ($event.target as HTMLInputElement).checked)"
          />
          <span class="text-xs text-slate-500">Enabled</span>
        </label>

        <select
          v-else-if="field.type === 'select'"
          :value="section.props[field.key] ?? ''"
          class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
          @change="onDiscreteFieldInput(field.key, ($event.target as HTMLSelectElement).value)"
        >
          <option value="">—</option>
          <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
        </select>

        <input
          v-else-if="field.type === 'media'"
          :value="section.props[field.key] ?? ''"
          type="number"
          placeholder="Media ID (picker UI not built yet)"
          class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"
          @input="onDiscreteFieldInput(field.key, Number(($event.target as HTMLInputElement).value) || null)"
        />
      </div>

      <p v-if="entry?.isDynamic" class="text-xs text-slate-400 mt-2">
        This section queries live content — changes here update the canvas preview automatically.
      </p>
    </div>
  </div>
</template>
