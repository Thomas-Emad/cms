<script setup lang="ts">
import { computed } from 'vue';
import { SECTION_REGISTRY } from './registry';
import type { Section, RenderMode } from '@/types/pageBuilder';

const props = defineProps<{
  section: Section;
  mode: RenderMode;
}>();

const entry = computed(() => SECTION_REGISTRY[props.section.type]);
</script>

<template>
  <div v-if="entry" :data-section-id="section.id" :data-section-type="section.type">
    <component
      :is="entry.component"
      :props="section.props"
      :settings="section.settings ?? {}"
      :data="section.data"
      :mode="mode"
    />
  </div>

  <!--
    Unknown type (e.g. a deprecated section type still present in old
    published JSON - see PageRenderService's defensive handling). Rendered
    as an empty placeholder in edit mode only, so an admin can see
    something needs attention; silent no-op in preview/live so a guest
    never sees broken chrome.
  -->
  <div v-else-if="mode === 'edit'" class="p-4 text-xs text-red-500 border border-dashed border-red-300 rounded">
    Unknown section type: {{ section.type }}
  </div>
</template>
