<script setup lang="ts">
import { computed } from 'vue';
import { SECTION_REGISTRY } from './registry';
import { useLocale } from '@/i18n';
import type { Section, RenderMode } from '@/types/pageBuilder';

const props = defineProps<{
  section: Section;
  mode: RenderMode;
}>();

const { locale } = useLocale();
const entry = computed(() => SECTION_REGISTRY[props.section.type]);

const resolvedProps = computed(() => {
  const p = { ...(props.section.props ?? {}) } as Record<string, any>;
  const loc = locale.value;
  if (loc !== 'en') {
    const secTrans = (props.section as any).translations?.[loc];
    if (secTrans?.props) {
      Object.assign(p, secTrans.props);
    }
    if (p.translations?.[loc]) {
      Object.assign(p, p.translations[loc]);
    }
    for (const key of Object.keys(p)) {
      const locKey = `${key}_${loc}`;
      if (p[locKey] !== undefined && p[locKey] !== '') {
        p[key] = p[locKey];
      }
    }
  }
  return p;
});
</script>

<template>
  <div v-if="entry" :data-section-id="section.id" :data-section-type="section.type">
    <component
      :is="entry.component"
      :props="resolvedProps"
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
