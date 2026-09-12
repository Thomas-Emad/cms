<script setup lang="ts">
import { provide } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ComponentLibrary from '@/PageBuilder/ComponentLibrary.vue';
import Canvas from '@/PageBuilder/Canvas.vue';
import SettingsPanel from '@/PageBuilder/SettingsPanel.vue';
import { usePageBuilderStore } from '@/PageBuilder/store';
import type { Section } from '@/types/pageBuilder';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  page: { id: number; name: string; slug: string; status: string; published_version_id: number | null };
  schemaVersion: number;
  sections: Section[];
  availableSectionTypes: string[];
}>();

const store = usePageBuilderStore(props.page.id, {
  schema_version: props.schemaVersion,
  sections: props.sections,
});
provide('pageBuilderStore', store);

function onKeydown(e: KeyboardEvent) {
  const meta = e.metaKey || e.ctrlKey;
  if (!meta) return;
  if (e.key === 'z' && !e.shiftKey) { e.preventDefault(); store.undo(); }
  else if (e.key === 'z' && e.shiftKey) { e.preventDefault(); store.redo(); }
  else if (e.key === 'y') { e.preventDefault(); store.redo(); }
}
</script>

<template>
  <Head :title="`Builder — ${props.page.name}`" />

  <div class="flex flex-col h-[calc(100vh-3.5rem)] -m-6" tabindex="0" @keydown="onKeydown">
    <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2">
      <div class="flex items-center gap-3">
        <Link
          href="/admin/pages"
          class="text-sm text-slate-400 hover:text-slate-700"
          title="Back to Pages"
        >
          ← Pages
        </Link>
        <span class="w-px h-5 bg-slate-200" />
        <h1 class="text-sm font-semibold text-slate-800">{{ props.page.name }}</h1>
        <span
          class="rounded-full px-2 py-0.5 text-xs"
          :class="props.page.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
        >
          {{ props.page.status }}
        </span>
        <span v-if="store.state.isDirty" class="text-xs text-amber-500">Unsaved changes</span>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          :disabled="!store.canUndo.value"
          title="Undo (Ctrl/Cmd+Z)"
          class="rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed"
          @click="store.undo()"
        >
          ↶ Undo
        </button>
        <button
          type="button"
          :disabled="!store.canRedo.value"
          title="Redo (Ctrl/Cmd+Shift+Z)"
          class="rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed"
          @click="store.redo()"
        >
          ↷ Redo
        </button>

        <span class="w-px h-5 bg-slate-200 mx-1" />

        <Link
          :href="`/admin/pages/${props.page.id}/preview`"
          class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
        >
          Preview
        </Link>
        <button
          type="button"
          :disabled="store.state.isSaving"
          class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-50"
          @click="store.saveDraft()"
        >
          {{ store.state.isSaving ? 'Saving…' : 'Save Draft' }}
        </button>
        <button
          type="button"
          class="rounded-md px-3 py-1.5 text-sm font-medium text-white"
          style="background: var(--color-primary, #1F4B5A)"
          @click="store.publish()"
        >
          Publish
        </button>
      </div>
    </div>

    <div class="flex flex-1 min-h-0">
      <ComponentLibrary />
      <Canvas />
      <SettingsPanel />
    </div>
  </div>
</template>
