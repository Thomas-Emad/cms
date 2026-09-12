<script setup lang="ts">
import { inject } from 'vue';
import { SECTION_REGISTRY } from './registry';
import type { PageBuilderStore } from './store';

const store = inject<PageBuilderStore>('pageBuilderStore')!;

const entries = Object.entries(SECTION_REGISTRY);
</script>

<template>
  <div class="w-56 shrink-0 border-r border-slate-200 bg-white p-3 overflow-y-auto">
    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Add Section</h2>
    <button
      v-for="[type, entry] in entries"
      :key="type"
      type="button"
      class="w-full text-left rounded-md px-3 py-2 mb-1 text-sm text-slate-700 hover:bg-slate-100 flex items-center justify-between"
      @click="store.addSection(type)"
    >
      {{ entry.label }}
      <span class="text-slate-300">+</span>
    </button>
  </div>
</template>
