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
            'h-6 lg:h-8': props.props.height === 'small',
            'h-12 lg:h-16': props.props.height === 'medium' || !props.props.height,
            'h-24 lg:h-32': props.props.height === 'large',
        }"
    />
</template>
