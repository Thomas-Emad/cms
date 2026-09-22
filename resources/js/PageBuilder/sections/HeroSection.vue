<script setup lang="ts">
import { ref } from 'vue';
import type { HeroProps, SectionSettings, RenderMode } from '@/types/pageBuilder';
import { useParallax, vRevealMount } from '@/lib/motion';

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
// area, so a real 100vh hero there would make editing unusable. Only go
// full-cinematic in the actual guest-facing render. The canvas also
// skips the mount-sequenced entrance entirely - an admin editing the
// hero shouldn't wait for a ~1s sequence to replay every time a prop
// changes and the section re-renders.
const isEditorCanvas = props.mode === 'edit';
</script>

<template>
    <section
        ref="heroRef"
        class="relative w-full overflow-hidden flex items-end"
        :class="isEditorCanvas ? 'h-[420px]' : undefined"
        :style="{
            background: !data?.media ? 'var(--luxury-forest)' : undefined,
            // Fills the viewport minus whatever dock is reserved at the
            // bottom (0 on the TV shell - see TvGuestLayout.vue). Not
            // subtracting topbar height too: the hero is meant to render
            // full-bleed BEHIND the fixed topbar (see FULL_BLEED_FIRST in
            // PageView.vue), not stop above it. The classic shell's dock
            // used to hide an 8vh gap here; without it, a fixed 92vh left
            // the next section visibly peeking in at the bottom.
            ...(isEditorCanvas ? {} : {
                height: 'calc(100vh - var(--kiosk-dock-h, 0px))',
                minHeight: '560px',
            }),
        }"
    >
        <!--
          Sequence, slowest/foundational first:
            1. image scale-settles
            2. dark atmosphere fades over it
            3. eyebrow, then heading (masked line-rise), then description,
               then CTA - each a little later than the last, so the scene
               reads as arriving rather than everything popping at once.
        -->
        <div class="absolute inset-0">
            <img
                v-if="data?.media"
                :src="data.media.url"
                :alt="data.media.alt_text ?? ''"
                :class="isEditorCanvas ? 'w-full h-full object-cover' : 'reveal-scale w-full h-full object-cover'"
                :style="isEditorCanvas ? {} : parallaxStyle()"
                v-reveal-mount="isEditorCanvas ? undefined : { delay: 0 }"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-white/40 text-sm">
                <span v-if="isEditorCanvas">No image selected</span>
            </div>

            <div
                class="absolute inset-0"
                :class="isEditorCanvas ? '' : 'reveal'"
                :style="{ background: 'var(--atmosphere-gradient)' }"
                v-reveal-mount="isEditorCanvas ? undefined : { delay: 150 }"
            />
        </div>

        <div class="relative z-10 w-full px-6 lg:px-10 pb-16 lg:pb-36" :class="{ 'pb-8': isEditorCanvas }">
            <div class="mx-auto max-w-7xl">
                <p
                    class="text-white/80 text-xs uppercase tracking-[0.24em] mb-4"
                    :class="isEditorCanvas ? '' : 'reveal'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 300 }"
                >
                    Grand Horizon
                </p>

                <!-- The heading gets the masked line-rise, not a plain fade -
                     it's the most important element on the page, and this is
                     what makes it feel like it's arriving into the scene
                     rather than fading like everything else. -->
                <div
                    :class="isEditorCanvas ? '' : 'reveal-mask'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 460 }"
                >
                    <h1
                        class="text-white font-normal leading-[1.05]"
                        :class="[isEditorCanvas ? 'text-3xl' : 'reveal-mask-inner text-5xl lg:text-8xl max-w-4xl']"
                        style="font-family: var(--font-display)"
                    >
                        {{ props.props.title }}
                    </h1>
                </div>

                <p
                    v-if="props.props.subtitle"
                    class="mt-4 text-white/85 max-w-lg"
                    :class="isEditorCanvas ? 'text-sm' : 'reveal text-lg lg:text-xl'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 640 }"
                >
                    {{ props.props.subtitle }}
                </p>

                <a
                    v-if="props.props.button_text && props.props.button_url"
                    :href="props.props.button_url"
                    class="group mt-8 inline-flex items-center gap-2 text-white text-sm uppercase tracking-wide border-b border-white/40 pb-1 hover:border-white transition-colors"
                    :class="isEditorCanvas ? '' : 'reveal'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 820 }"
                >
                    {{ props.props.button_text }}
                    <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
                </a>
            </div>
        </div>

        <!--
          Scroll indicator: a slow, quiet drift-and-fade loop rather than
          Tailwind's animate-bounce, which reads as playful rather than
          cinematic for a premium hotel scene.
        -->
        <div
            v-if="!isEditorCanvas"
            class="reveal absolute bottom-6 left-1/2 -translate-x-1/2 z-10 text-white/70 scroll-cue"
            v-reveal-mount="{ delay: 1000 }"
            aria-hidden="true"
        >
            <div class="w-px h-8 bg-white/40 mx-auto" />
        </div>
    </section>
</template>

<style scoped>
/* A slow (3.2s) opacity/translate drift, not a bounce - quiet enough to
   read as ambient rather than an animated UI control. Covered by the
   global prefers-reduced-motion rule in app.css (animation-duration
   override applies to all animations, not just transitions). */
.scroll-cue {
    animation: scroll-cue-drift 3.2s var(--ease-standard) infinite;
}
@keyframes scroll-cue-drift {
    0%,
    100% {
        transform: translate(-50%, 0);
        opacity: 0.7;
    }
    50% {
        transform: translate(-50%, 6px);
        opacity: 0.35;
    }
}
</style>
