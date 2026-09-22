<script setup lang="ts">
import { computed } from 'vue';
import type { AppLauncherProps, SectionSettings, RenderMode } from '@/types/pageBuilder';

interface AppLauncherData {
    media?: { url: string; alt_text: string | null } | null;
}

const props = defineProps<{
    props: AppLauncherProps;
    settings: SectionSettings;
    data?: AppLauncherData;
    mode: RenderMode;
}>();

type Tile = { label: string; url: string | null };

/**
 * One app tile per line: "Label | URL" (URL optional - a line with no
 * "|" is just a non-clickable label, e.g. "Apps"). Same convention as
 * InfoListSection's items_text: only the FIRST "|" splits, plain text
 * interpolation only, never v-html. Parsed client-side so no repeater
 * field type is needed in the Builder - see AppLauncherSectionDefinition.
 */
const tiles = computed<Tile[]>(() =>
    (props.props.apps_text ?? '')
        .split('\n')
        .map((l) => l.trim())
        .filter(Boolean)
        .map((line): Tile => {
            const at = line.indexOf('|');
            if (at === -1) return { label: line, url: null };
            return { label: line.slice(0, at).trim(), url: line.slice(at + 1).trim() || null };
        }),
);

// Same canvas-vs-live distinction as HeroSection: the Builder canvas
// stacks many sections in a small scroll area, so this always renders at
// a fixed preview height there. On the actual guest screen (or when the
// page's layout is 'fullscreen' - see PageView.vue) it fills the screen.
const isEditorCanvas = props.mode === 'edit';
</script>

<template>
    <section class="relative w-full overflow-hidden flex flex-col justify-end"
        :class="isEditorCanvas ? 'h-[420px]' : 'h-full min-h-screen'"
        :style="{ background: !data?.media ? 'var(--luxury-forest)' : undefined }">
        <div class="absolute inset-0">
            <img v-if="data?.media" :src="data.media.url" :alt="data.media.alt_text ?? ''"
                class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center text-white/40 text-sm">
                <span v-if="isEditorCanvas">No background image selected</span>
            </div>
            <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
        </div>

        <div class="relative z-10 px-6 lg:px-10 pb-6 lg:pb-10">
            <p v-if="props.props.eyebrow" class="text-white/80 text-xs uppercase tracking-[0.24em] mb-2">
                {{ props.props.eyebrow }}
            </p>
            <h1 v-if="props.props.title" class="text-white font-normal leading-tight mb-1"
                :class="isEditorCanvas ? 'text-2xl' : 'text-4xl lg:text-6xl'" style="font-family: var(--font-display)">
                {{ props.props.title }}
            </h1>
            <p v-if="props.props.subtitle" class="text-white/85"
                :class="isEditorCanvas ? 'text-xs' : 'text-base lg:text-lg'">
                {{ props.props.subtitle }}
            </p>
        </div>

        <!--
          App row: fixed tiles that wrap rather than scroll horizontally -
          matches the reference (Apps / YouTube / Netflix / Prime Video /
          Disney+) sitting edge-to-edge on one screen with no scrollbar,
          which matters here since this section is meant for a
          'fullscreen' page (see PageView.vue) that has no scroll at all.
        -->
        <div v-if="tiles.length" class="relative z-10 flex flex-wrap gap-3 px-6 lg:px-10 pb-8 lg:pb-12">
            <a v-for="(tile, i) in tiles" :key="i" :href="tile.url || undefined"
                class="flex items-center justify-center rounded-lg bg-white/95 px-4 py-3 min-w-[110px] shadow-sm hover:bg-white transition-colors"
                :class="{ 'cursor-default': !tile.url }">
                <span class="text-sm font-medium text-slate-800">{{ tile.label }}</span>
            </a>
        </div>
        <p v-else-if="isEditorCanvas" class="relative z-10 px-6 pb-6 text-xs text-white/50">
            No app tiles yet. One per line: Label | URL
        </p>
    </section>
</template>
