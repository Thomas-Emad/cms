<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';

interface Img {
    url: string;
    alt_text?: string | null;
    type?: 'image' | 'video';
}

const props = withDefaults(defineProps<{ images: Img[]; columns?: 2 | 3 | 4 }>(), { columns: 3 });

/**
 * Photo grid with a full-screen viewer. Touch friendly: tap a tile to open,
 * swipe or use the big arrows to move, tap × or the dark backdrop to close.
 * The viewer is teleported to <body> so it sits above the fixed top bar and dock.
 */
const active = ref<number | null>(null);
const isOpen = computed(() => active.value !== null);

function open(i: number) {
    active.value = i;
}
function close() {
    active.value = null;
}
function step(dir: -1 | 1) {
    if (active.value === null) return;
    const n = props.images.length;
    active.value = (active.value + dir + n) % n;
}

/* swipe */
let startX = 0;
function onDown(e: PointerEvent) {
    startX = e.clientX;
}
function onUp(e: PointerEvent) {
    const dx = e.clientX - startX;
    if (Math.abs(dx) > 60) step(dx < 0 ? 1 : -1);
}

function onKey(e: KeyboardEvent) {
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowRight') step(1);
    else if (e.key === 'ArrowLeft') step(-1);
}

// Lock page scroll while the viewer is open.
watch(isOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) window.addEventListener('keydown', onKey);
    else window.removeEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKey);
});

const colClass = computed(() => ({ 2: 'grid-cols-2', 3: 'grid-cols-3', 4: 'grid-cols-4' })[props.columns]);
</script>

<template>
    <div>
        <div class="grid gap-4" :class="colClass">
            <button
                v-for="(img, i) in images"
                :key="i"
                type="button"
                class="relative aspect-[4/3] overflow-hidden bg-slate-100 active:scale-[0.98] transition-transform duration-150"
                data-testid="tile"
                @click="open(i)"
            >
                <!-- Videos show their first frame as the thumbnail, with a play badge. -->
                <video v-if="img.type === 'video'" :src="img.url" muted playsinline preload="metadata" class="absolute inset-0 h-full w-full object-cover" />
                <img v-else :src="img.url" :alt="img.alt_text ?? ''" draggable="false" class="absolute inset-0 h-full w-full object-cover" />
                <span v-if="img.type === 'video'" class="absolute inset-0 flex items-center justify-center" aria-label="Video">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-black/55 text-white text-2xl">▶</span>
                </span>
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[70] flex items-center justify-center bg-black/95"
                data-testid="viewer"
                @click.self="close"
            >
                <video
                    v-if="images[active!].type === 'video'"
                    :key="active!"
                    :src="images[active!].url"
                    autoplay
                    controls
                    playsinline
                    class="max-h-full max-w-full"
                />
                <img
                    v-else
                    :src="images[active!].url"
                    :alt="images[active!].alt_text ?? ''"
                    draggable="false"
                    class="max-h-full max-w-full object-contain select-none"
                    @pointerdown="onDown"
                    @pointerup="onUp"
                />

                <button type="button" class="absolute top-6 right-6 h-16 w-16 rounded-full bg-white/15 text-white text-4xl" aria-label="Close" data-testid="close" @click="close">×</button>

                <template v-if="images.length > 1">
                    <button type="button" class="absolute left-6 top-1/2 -translate-y-1/2 h-20 w-20 rounded-full bg-white/15 text-white text-5xl" aria-label="Previous" data-testid="prev" @click.stop="step(-1)">‹</button>
                    <button type="button" class="absolute right-6 top-1/2 -translate-y-1/2 h-20 w-20 rounded-full bg-white/15 text-white text-5xl" aria-label="Next" data-testid="next" @click.stop="step(1)">›</button>
                    <p class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-lg tabular-nums">{{ active! + 1 }} / {{ images.length }}</p>
                </template>
            </div>
        </Teleport>
    </div>
</template>
