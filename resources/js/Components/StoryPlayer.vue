<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

export interface StorySlide {
    url: string | null;
    type?: 'image' | 'video';
    alt_text?: string | null;
}

const props = withDefaults(
    defineProps<{
        slides: StorySlide[];
        /** Seconds each PHOTO stays on screen. Videos play to their end instead. */
        intervalSeconds?: number;
        /** CSS height of the player. */
        height?: string;
        /** Builder canvas: static and compact, never animates. */
        editor?: boolean;
        /** Re-run the text entrance animation on every slide (for per-slide captions). */
        animateOverlay?: boolean;
    }>(),
    { intervalSeconds: 6, height: '100%', editor: false, animateOverlay: false },
);

/**
 * Story-style player used by the home slideshow, Rooms, Gallery and detail pages.
 *   - photos auto-advance (progress bar per slide), slow "Ken Burns" zoom
 *   - videos play muted and advance when they finish
 *   - tap right = next, tap left = previous, press-and-hold = pause
 *   - pauses when scrolled off-screen or the tab is hidden
 * Overlay content (titles, buttons) goes in the default slot; it receives the
 * current `index`. Interactive things in the slot need `pointer-events-auto`.
 */
const count = computed(() => props.slides.length);
const durationMs = computed(() => Math.min(20, Math.max(3, props.intervalSeconds)) * 1000);

const index = ref(0);
const progress = ref(0);
const paused = ref(false);
const inView = ref(true);

const rootRef = ref<HTMLElement | null>(null);
const videos: Record<number, HTMLVideoElement | null> = {};

function setVideo(i: number, el: unknown) {
    videos[i] = (el as HTMLVideoElement | null) ?? null;
}

// jsdom / old browsers: play() may return undefined instead of a promise.
function safePlay(v: HTMLVideoElement) {
    try {
        (v.play() as Promise<void> | undefined)?.catch?.(() => {});
    } catch {
        /* autoplay refused - the slide simply stays on its first frame */
    }
}

function next() {
    if (count.value < 2) return;
    index.value = (index.value + 1) % count.value;
    progress.value = 0;
}

function prev() {
    if (count.value < 2) return;
    // Like stories: near the start of a slide, go back one; otherwise restart it.
    if (progress.value > 0.15) {
        restartCurrentVideo();
        progress.value = 0;
        return;
    }
    index.value = (index.value - 1 + count.value) % count.value;
    progress.value = 0;
}

function restartCurrentVideo() {
    const v = videos[index.value];
    if (v) v.currentTime = 0;
}

function syncVideos() {
    props.slides.forEach((_, i) => {
        const v = videos[i];
        if (!v) return;
        if (i === index.value && !props.editor) {
            v.currentTime = 0;
            safePlay(v);
        } else {
            v.pause();
        }
    });
}
watch(index, () => nextTick(syncVideos));

/* --- clock --- */
let raf = 0;
let last = 0;

function tick(t: number) {
    if (last === 0) last = t;
    const dt = Math.min(t - last, 100); // clamp: a background tab must not skip several slides
    last = t;

    const active = !paused.value && inView.value && !document.hidden;
    const slide = props.slides[index.value];
    const video = slide?.type === 'video' ? videos[index.value] : null;

    if (video) {
        if (active) {
            if (video.paused && !video.ended) safePlay(video);
            if (video.duration > 0 && isFinite(video.duration)) progress.value = Math.min(1, video.currentTime / video.duration);
            if (video.ended && count.value > 1) next();
        } else if (!video.paused) {
            video.pause();
        }
    } else if (active && count.value > 1) {
        progress.value += dt / durationMs.value;
        if (progress.value >= 1) next();
    }

    raf = requestAnimationFrame(tick);
}

/* --- touch --- */
let downAt = 0;
function onDown() {
    downAt = performance.now();
    paused.value = true;
}
function onUp(e: PointerEvent) {
    paused.value = false;
    if (performance.now() - downAt > 250) return; // a hold, not a tap
    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const isRtl = typeof document !== 'undefined' && document.documentElement.dir === 'rtl';
    if (isRtl) {
        if (x > 0.7) prev();
        else next();
    } else {
        if (x < 0.3) prev();
        else next();
    }
}
function onCancel() {
    paused.value = false;
}

let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (props.editor) return;
    observer = new IntersectionObserver(([entry]) => (inView.value = entry.isIntersecting), { threshold: 0.3 });
    if (rootRef.value) observer.observe(rootRef.value);
    syncVideos();
    raf = requestAnimationFrame(tick);
});

onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    observer?.disconnect();
});

function barWidth(i: number): string {
    if (props.editor) return i === 0 ? '100%' : '0%';
    if (i < index.value) return '100%';
    if (i === index.value) return `${Math.min(progress.value, 1) * 100}%`;
    return '0%';
}
</script>

<template>
    <section
        ref="rootRef"
        class="relative w-full overflow-hidden"
        :style="{ height: editor ? '420px' : height, minHeight: editor || height === '100%' ? undefined : '420px', background: 'var(--luxury-forest)' }"
    >
        <template v-for="(slide, i) in slides" :key="i">
            <video
                v-if="slide.type === 'video' && slide.url"
                :ref="(el) => setVideo(i, el)"
                :src="slide.url"
                muted
                playsinline
                preload="auto"
                :loop="count === 1"
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 motion-reduce:transition-none"
                :class="i === index ? 'opacity-100' : 'opacity-0'"
            />
            <img
                v-else-if="slide.url"
                :src="slide.url"
                :alt="slide.alt_text ?? ''"
                draggable="false"
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 motion-reduce:transition-none"
                :class="i === index ? 'opacity-100' : 'opacity-0'"
                :style="{ transform: i === index && !editor ? `scale(${1 + progress * 0.06})` : undefined }"
            />
        </template>

        <!-- legibility gradient -->
        <div class="absolute inset-0 pointer-events-none" style="background: var(--atmosphere-gradient)" />

        <!-- tap / hold surface (under the text so nothing blocks it) -->
        <div
            v-if="!editor"
            class="absolute inset-0 z-10"
            @pointerdown="onDown"
            @pointerup="onUp"
            @pointercancel="onCancel"
            @pointerleave="onCancel"
        />

        <!-- one progress bar per slide, just below the fixed top bar -->
        <div
            v-if="count > 1"
            class="absolute inset-x-0 z-20 flex gap-2 px-10 pointer-events-none"
            :style="{ top: editor ? '0.75rem' : 'calc(var(--kiosk-topbar-h, 0px) + 0.75rem)' }"
            aria-hidden="true"
        >
            <div v-for="(_, i) in slides" :key="i" class="h-1 flex-1 rounded-full bg-white/30 overflow-hidden">
                <div class="h-full bg-white" :style="{ width: barWidth(i) }" />
            </div>
        </div>

        <!-- overlay content, anchored bottom-left like a film title card -->
        <div class="absolute inset-x-0 bottom-0 z-20 px-10 pb-12 pointer-events-none">
            <div class="mx-auto max-w-7xl">
                <Transition v-if="animateOverlay" name="story-text" mode="out-in">
                    <div :key="index"><slot :index="index" :count="count" /></div>
                </Transition>
                <slot v-else :index="index" :count="count" />
            </div>
        </div>
    </section>
</template>
