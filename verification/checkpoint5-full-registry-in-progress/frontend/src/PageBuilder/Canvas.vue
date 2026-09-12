<script setup lang="ts">
import { inject, computed } from 'vue';
import draggable from 'vuedraggable';
import SectionRenderer from './SectionRenderer.vue';
import type { PageBuilderStore } from './store';
import type { Section } from '@/types/pageBuilder';

const store = inject<PageBuilderStore>('pageBuilderStore')!;

/**
 * vuedraggable's v-model needs a get/set pair rather than binding
 * store.state.sections directly, so that every drop goes through
 * store.setSectionsOrder() - which is what applies the history snapshot
 * and dirty flag. This is still the SAME underlying array; the computed
 * is just the single, tested seam through which reordering is allowed to
 * happen, rather than letting the drag library mutate state.sections
 * directly and bypassing history/dirty tracking.
 */
const sections = computed<Section[]>({
  get: () => store.state.sections,
  set: (value) => store.setSectionsOrder(value),
});
</script>

<template>
  <div class="flex-1 overflow-y-auto bg-slate-100 p-6">
    <div class="mx-auto max-w-screen-sm bg-white shadow-sm min-h-[400px]">
      <div v-if="!store.state.sections.length" class="p-12 text-center text-sm text-slate-400">
        No sections yet — add one from the library on the left.
      </div>

      <draggable
        v-model="sections"
        item-key="id"
        handle=".drag-handle"
        ghost-class="opacity-40"
        :animation="150"
      >
        <template #item="{ element: section }: { element: Section }">
          <div
            class="relative group border-2"
            :class="store.state.selectedSectionId === section.id
              ? 'border-blue-400 ring-1 ring-blue-200'
              : 'border-transparent hover:border-slate-200'"
            @click="store.selectSection(section.id)"
          >
            <!--
              Rendering is entirely delegated to SectionRenderer - the
              same component full Preview and the live Guest page use.
              This wrapper only adds edit-mode chrome around it.
            -->
            <SectionRenderer :section="section" mode="edit" />

            <div class="absolute top-1 right-1 hidden group-hover:flex items-center gap-1 bg-white rounded shadow px-1 py-0.5">
              <span
                class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 px-1 select-none"
                title="Drag to reorder"
              >
                ⠿
              </span>
              <span class="text-xs text-slate-400 px-1">{{ section.type }}</span>
              <button
                type="button"
                class="text-xs text-slate-500 hover:text-slate-800 px-1"
                @click.stop="store.duplicateSection(section.id)"
              >
                Duplicate
              </button>
              <button
                type="button"
                class="text-xs text-red-500 hover:text-red-700 px-1"
                @click.stop="store.removeSection(section.id)"
              >
                Remove
              </button>
            </div>
          </div>
        </template>
      </draggable>
    </div>
  </div>
</template>
