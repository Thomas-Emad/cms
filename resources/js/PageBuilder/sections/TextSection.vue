<script setup lang="ts">
import type { TextProps, SectionSettings, RenderMode } from '@/types/pageBuilder';

defineProps<{
  props: TextProps;
  settings: SectionSettings;
  mode: RenderMode;
}>();
</script>

<template>
  <section
    class="mx-auto max-w-screen-sm px-4"
    :class="{
      'py-12': settings.padding === 'large',
      'py-6': settings.padding === 'medium' || !settings.padding,
      'py-3': settings.padding === 'small',
      'py-0': settings.padding === 'none',
    }"
  >
    <h2 v-if="props.heading" class="text-lg font-semibold text-slate-800 mb-2">{{ props.heading }}</h2>
    <!--
      Plain text interpolation only - never v-html. `body` is stored and
      validated as plain text server-side (see TextSectionDefinition);
      white-space: pre-line preserves line breaks without needing to
      parse/render any markup.
    -->
    <p class="text-sm text-slate-600" style="white-space: pre-line">{{ props.body }}</p>
  </section>
</template>
