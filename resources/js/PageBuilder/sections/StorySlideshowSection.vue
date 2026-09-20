<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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

/**
 * "Story" slideshow: photos auto-advance, one progress bar per slide at the
 * top. Tap the right side = next, left side = previous, press-and-hold =
 * pause (same grammar guests already know from Instagram/WhatsApp stories).
 *
 * In the Builder canvas (mode === 'edit') it stays static and compact so
 * editing isn't fighting an animation - same rule as HeroSection.
 */
const isEditorCanvas = props.mode === 'edit';

const slides = computed(() => props.data?.media ?? []);
const count = computed(() => slides.value.length);
const durationMs = computed(() => Math.min(20, Math.max(3, props.props.interval_seconds ?? 6)) * 1000);

const index = ref(0);
const progress = ref(0); // 0..1 for the current slide
const paused = ref(false);
const inView = ref(true);

const rootRef = ref<HTMLElement | null>(null);

function next() {
    if (count.value < 2) return;
    index.value = (index.value + 1) % count.value;
    progress.value = 0;
}

function prev() {
    if (count.value < 2) return;
    // Like stories: if the current slide has barely started, go back one;
    // otherwise first tap restarts the current slide.
    if (progress.value > 0.15) {
        progress.value = 0;
        return;
    }
    index.value = (index.value - 1 + count.value) % count.value;
    progress.value = 0;
}

/* --- clock: requestAnimationFrame, paused while held / offscreen / hidden --- */
let raf = 0;
let last = 0;

function tick(t: number) {
    if (last === 0) last = t;
    // Clamp so returning from a background tab doesn't skip several slides.
    const dt = Math.min(t - last, 100);
    last = t;

    if (!paused.value && inView.value && count.value > 1 && !document.hidden) {
        progress.value += dt / durationMs.value;
        if (progress.value >= 1) next();
    }
    raf = requestAnimationFrame(tick);
}

/* --- touch: tap zones + hold to pause --- */
let downAt = 0;

function onDown() {
    downAt = performance.now();
    paused.value = true;
}
function onUp(e: PointerEvent) {
    paused.value = false;
    if (performance.now() - downAt > 250) return; // it was a hold, not a tap
    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    if (x < 0.3) prev();
    else next();
}
function onCancel() {
    paused.value = false;
}

let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (isEditorCanvas) return;
    observer = new IntersectionObserver(([entry]) => (inView.value = entry.isIntersecting), { threshold: 0.3 });
    if (rootRef.value) observer.observe(rootRef.value);
    raf = requestAnimationFrame(tick);
});

onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    observer?.disconnect();
});

function barWidth(i: number): string {
    if (isEditorCanvas) return i === 0 ? '100%' : '0%';
    if (i < index.value) return '100%';
    if (i === index.value) return `${Math.min(progress.value, 1) * 100}%`;
    return '0%';
}
</script>

<template>
    <section
        ref="rootRef"
        class="relative w-full overflow-hidden"
        :style="{
            height: isEditorCanvas ? '420px' : 'calc(100dvh - var(--kiosk-dock-h, 0px))',
            minHeight: isEditorCanvas ? undefined : '420px',
            background: 'var(--luxury-forest)',
        }"
    >
        <!-- Slides: stacked, cross-fading. Active one drifts slowly (Ken Burns) tied to its progress. -->
        <img
            v-for="(item, i) in slides"
            :key="i"
            :src="item.url"
            :alt="item.alt_text ?? ''"
            draggable="false"
            class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 motion-reduce:transition-none"
            :class="i === index ? 'opacity-100' : 'opacity-0'"
            :style="{
                transform: i === index && !isEditorCanvas ? `scale(${1 + progress * 0.06})` : undefined,
            }"
        />
        <div v-if="!slides.length" class="absolute inset-0 flex items-center justify-center text-white/40 text-sm">
            <span v-if="isEditorCanvas">No images selected</span>
        </div>

        <!-- Legibility gradient over any photo -->
        <div class="absolute inset-0 pointer-events-none" style="background: var(--atmosphere-gradient)" />

        <!-- Tap / hold surface. Sits under the text and bars so they never block taps. -->
        <div
            v-if="!isEditorCanvas"
            class="absolute inset-0 z-10"
            @pointerdown="onDown"
            @pointerup="onUp"
            @pointercancel="onCancel"
            @pointerleave="onCancel"
        />

        <!-- Progress bars, just below the fixed top bar -->
        <div
            v-if="count > 1"
            class="absolute inset-x-0 z-20 flex gap-2 px-10 pointer-events-none"
            :style="{ top: isEditorCanvas ? '0.75rem' : 'calc(var(--kiosk-topbar-h, 0px) + 0.75rem)' }"
            aria-hidden="true"
        >
            <div v-for="(_, i) in slides" :key="i" class="h-1 flex-1 rounded-full bg-white/30 overflow-hidden">
                <div class="h-full bg-white" :style="{ width: barWidth(i) }" />
            </div>
        </div>

        <!-- Title block, anchored bottom-left like a film title card -->
        <div class="absolute inset-x-0 bottom-0 z-20 px-10 pb-12 pointer-events-none">
            <div class="mx-auto max-w-7xl">
                <h1
                    v-if="props.props.title"
                    class="text-white font-normal leading-[1.05] max-w-4xl"
                    :class="isEditorCanvas ? 'text-3xl' : 'text-6xl'"
                    style="font-family: var(--font-display)"
                >
                    {{ props.props.title }}
                </h1>
                <p
                    v-if="props.props.subtitle"
                    class="mt-4 text-white/85 max-w-2xl"
                    :class="isEditorCanvas ? 'text-sm' : 'text-2xl'"
                >
                    {{ props.props.subtitle }}
                </p>
            </div>
        </div>
    </section>
</template>
