<script setup lang="ts">
import type { HeroProps, SectionSettings, RenderMode } from '@/types/pageBuilder';

defineProps<{
  props: HeroProps;
  settings: SectionSettings;
  mode: RenderMode;
}>();
</script>

<template>
  <section
    class="relative flex flex-col items-center justify-center text-center px-4"
    :class="{
      'py-16': settings.padding === 'large',
      'py-8': settings.padding === 'medium' || !settings.padding,
      'py-4': settings.padding === 'small',
      'py-0': settings.padding === 'none',
    }"
    :style="{
      background: settings.background === 'brand' ? 'var(--color-primary)' : undefined,
      color: settings.background === 'brand' || settings.background === 'dark' ? 'white' : undefined,
    }"
  >
    <h1 class="text-2xl font-semibold" :style="{ color: settings.background ? undefined : 'var(--color-primary, #1F4B5A)' }">
      {{ props.title }}
    </h1>
    <p v-if="props.subtitle" class="mt-2 text-sm opacity-80">{{ props.subtitle }}</p>
    <a
      v-if="props.button_text && props.button_url"
      :href="props.button_url"
      class="mt-4 inline-block px-4 py-2 text-sm font-medium text-white"
      style="background: var(--color-primary, #1F4B5A); border-radius: var(--radius, 8px)"
    >
      {{ props.button_text }}
    </a>
  </section>
</template>
