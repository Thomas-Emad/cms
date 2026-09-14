<script setup lang="ts">
import { ref } from 'vue';
import type { HeroProps, SectionSettings, RenderMode } from '@/types/pageBuilder';
import { useParallax } from '@/lib/motion';

interface HeroData {
    media?: { url: string; alt_text: string | null } | null;
}

const props = defineProps<{
    props: HeroProps;
    settings: SectionSettings;
    data?: HeroData;
    mode: RenderMode;
}>();

const heroRef = ref<HTMLElement | null>(null);
const { style: parallaxStyle } = useParallax(heroRef, { strength: 0.12 });

// The Builder canvas renders many sections stacked in a small scroll
// area, so a real 100vh hero there would make editing unusable. Only
// go full-cinematic in the actual guest-facing render.
const isEditorCanvas = props.mode === 'edit';
</script>

<template>
    <section
        ref="heroRef"
        class="relative w-full overflow-hidden flex items-end"
        :class="isEditorCanvas ? 'h-[420px]' : 'h-[92vh] min-h-[560px]'"
        :style="{
            background: !data?.media ? (settings.background === 'brand' ? 'var(--luxury-forest)' : 'var(--luxury-forest)') : undefined,
        }"
    >
        <!-- Background imagery, drifting slightly slower than scroll -->
        <div class="absolute inset-0">
            <img
                v-if="data?.media"
                :src="data.media.url"
                :alt="data.media.alt_text ?? ''"
                class="reveal-scale is-visible w-full h-full object-cover"
                :style="isEditorCanvas ? {} : parallaxStyle()"
            />
            <div
                v-else
                class="w-full h-full flex items-center justify-center text-white/40 text-sm"
            >
                <span v-if="isEditorCanvas">No image selected</span>
            </div>
            <!-- Atmosphere: darkens toward the bottom so title/CTA stay legible
           over any photo without needing a flat color block. -->
            <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
        </div>

        <!-- Content, anchored toward the bottom third like a film title card -->
        <div class="relative z-10 w-full px-6 lg:px-10 pb-16 lg:pb-24" :class="{ 'pb-8': isEditorCanvas }">
            <div class="mx-auto max-w-7xl">
                <p
                    v-if="settings.padding"
                    class="reveal is-visible text-white/80 text-xs uppercase tracking-[0.24em] mb-4"
                    v-reveal
                >
                    Grand Horizon
                </p>
                <h1
                    class="reveal is-visible text-white font-normal leading-[1.05]"
                    :class="isEditorCanvas ? 'text-3xl' : 'text-5xl lg:text-8xl max-w-4xl'"
                    style="font-family: var(--font-display)"
                    v-reveal="{ delay: 80 }"
                >
                    {{ props.props.title }}
                </h1>
                <p
                    v-if="props.props.subtitle"
                    class="reveal is-visible mt-4 text-white/85 max-w-lg"
                    :class="isEditorCanvas ? 'text-sm' : 'text-lg lg:text-xl'"
                    v-reveal="{ delay: 180 }"
                >
                    {{ props.props.subtitle }}
                </p>
                <a
                    v-if="props.props.button_text && props.props.button_url"
                    :href="props.props.button_url"
                    class="reveal is-visible group mt-8 inline-flex items-center gap-2 text-white text-sm uppercase tracking-wide border-b border-white/40 pb-1 hover:border-white transition-colors"
                    v-reveal="{ delay: 280 }"
                >
                    {{ props.props.button_text }}
                    <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
                </a>
            </div>
        </div>

        <!-- Scroll indicator: a small cue that there's more below, not a UI control -->
        <div
            v-if="!isEditorCanvas"
            class="reveal absolute bottom-6 left-1/2 -translate-x-1/2 z-10 text-white/70 animate-bounce"
            style="animation-duration: 2s"
            v-reveal="{ delay: 500 }"
            aria-hidden="true"
        >
            <div class="w-px h-8 bg-white/40 mx-auto" />
        </div>
    </section>
</template>
