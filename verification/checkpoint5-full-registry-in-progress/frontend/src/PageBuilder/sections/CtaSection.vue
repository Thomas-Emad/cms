<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface CtaProps {
  heading?: string;
  subheading?: string;
  button_text?: string;
  button_url?: string;
}

defineProps<{
  props: CtaProps;
  settings: SectionSettings;
  mode: RenderMode;
}>();
</script>

<template>
  <section
    class="text-center px-4 py-10"
    :style="{ background: settings.background === 'brand' ? 'var(--color-primary, #1F4B5A)' : '#f8fafc' }"
  >
    <h2
      class="text-xl font-semibold"
      :style="{ color: settings.background === 'brand' ? 'white' : 'var(--color-primary, #1F4B5A)' }"
    >
      {{ props.heading }}
    </h2>
    <p v-if="props.subheading" class="mt-1 text-sm" :class="settings.background === 'brand' ? 'text-white/80' : 'text-slate-500'">
      {{ props.subheading }}
    </p>
    <a
      v-if="props.button_text && props.button_url"
      :href="props.button_url"
      class="mt-4 inline-block px-5 py-2 text-sm font-medium"
      :style="{
        background: settings.background === 'brand' ? 'white' : 'var(--color-primary, #1F4B5A)',
        color: settings.background === 'brand' ? 'var(--color-primary, #1F4B5A)' : 'white',
        borderRadius: 'var(--radius, 8px)',
      }"
    >
      {{ props.button_text }}
    </a>
  </section>
</template>
