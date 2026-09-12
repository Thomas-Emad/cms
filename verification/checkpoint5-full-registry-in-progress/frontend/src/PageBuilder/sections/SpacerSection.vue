<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface SpacerProps {
  height?: 'small' | 'medium' | 'large';
}

const props = defineProps<{
  props: SpacerProps;
  settings: SectionSettings;
  mode: RenderMode;
}>();
</script>

<template>
  <div
    :class="{
      'h-4': props.props.height === 'small',
      'h-8': props.props.height === 'medium' || !props.props.height,
      'h-16': props.props.height === 'large',
    }"
  />
</template>
