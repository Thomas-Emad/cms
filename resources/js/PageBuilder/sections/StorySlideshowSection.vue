<script setup lang="ts">
import { computed } from 'vue';
import StoryPlayer from '@/Components/StoryPlayer.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface StoryProps {
    title?: string;
    subtitle?: string;
    media_ids?: number[];
    interval_seconds?: number | null;
}
interface StoryData {
    media?: { url: string; alt_text: string | null }[];
}

const props = defineProps<{
    props: StoryProps;
    settings: SectionSettings;
    data?: StoryData;
    mode: RenderMode;
}>();

// The player itself lives in Components/StoryPlayer.vue (shared with Rooms, Gallery and
// detail pages). This section only maps Page Builder props onto it.
const isEditorCanvas = props.mode === 'edit';
const slides = computed(() => (props.data?.media ?? []).map((m) => ({ url: m.url, type: 'image' as const, alt_text: m.alt_text })));
</script>

<template>
    <StoryPlayer
        :slides="slides"
        :interval-seconds="props.props.interval_seconds ?? 6"
        :editor="isEditorCanvas"
        :height="'calc(100dvh - var(--kiosk-dock-h, 0px))'"
    >
        <h1
            v-if="props.props.title"
            class="text-white font-normal leading-[1.05] max-w-4xl"
            :class="isEditorCanvas ? 'text-3xl' : 'text-6xl'"
            style="font-family: var(--font-display)"
        >
            {{ props.props.title }}
        </h1>
        <p v-if="props.props.subtitle" class="mt-4 text-white/85 max-w-2xl" :class="isEditorCanvas ? 'text-sm' : 'text-2xl'">
            {{ props.props.subtitle }}
        </p>
    </StoryPlayer>
</template>
