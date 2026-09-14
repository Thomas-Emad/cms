<script setup lang="ts">
import { inject, computed } from 'vue';
import { sectionsForContext, type SectionContext } from './registry';
import type { PageBuilderStore } from './store';

const props = withDefaults(defineProps<{ context?: SectionContext }>(), { context: 'page' });

const store = inject<PageBuilderStore>('pageBuilderStore')!;

const entries = computed(() => sectionsForContext(props.context));

/** Splits into a primary group (matching the exact context, e.g.
 * 'restaurant') and a general group ('any') - mirrors the requested
 * "Restaurant / General" and "Layout / Content" groupings without
 * hardcoding per-context section lists here; it's derived purely from
 * each entry's own `contexts` metadata. */
const primaryEntries = computed(() => entries.value.filter(([, e]) => e.contexts.includes(props.context)));
const generalEntries = computed(() => entries.value.filter(([, e]) => !e.contexts.includes(props.context)));

const groupLabel = computed(() => (props.context === 'page' ? 'Layout & Content' : `${props.context.charAt(0).toUpperCase()}${props.context.slice(1)}`));
</script>

<template>
  <div class="w-56 shrink-0 border-r border-slate-200 bg-white p-3 overflow-y-auto">
    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">{{ groupLabel }}</h2>
    <button
      v-for="[type, entry] in primaryEntries"
      :key="type"
      type="button"
      class="w-full text-left rounded-md px-3 py-2 mb-1 text-sm text-slate-700 hover:bg-slate-100 flex items-center justify-between"
      @click="store.addSection(type)"
    >
      <span>{{ entry.icon }} {{ entry.label }}</span>
      <span class="text-slate-300">+</span>
    </button>

    <template v-if="generalEntries.length">
      <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-400 mt-4 mb-2">General</h2>
      <button
        v-for="[type, entry] in generalEntries"
        :key="type"
        type="button"
        class="w-full text-left rounded-md px-3 py-2 mb-1 text-sm text-slate-700 hover:bg-slate-100 flex items-center justify-between"
        @click="store.addSection(type)"
      >
        <span>{{ entry.icon }} {{ entry.label }}</span>
        <span class="text-slate-300">+</span>
      </button>
    </template>
  </div>
</template>
