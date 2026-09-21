<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import type { MapFloor } from './types';

const props = defineProps<{
    /** Highest floor first. */
    floors: MapFloor[];
    current: string;
    /** Floors the active route touches (shown with a dot). */
    routeFloors: Set<string>;
}>();
const emit = defineEmits<{ (e: 'select', id: string): void }>();

const SIZE = 46; // px, large touch target
const GAP = 6;
const nav = ref<HTMLElement | null>(null);
watch(() => props.current, async () => {
    await nextTick();
    nav.value?.querySelector('[aria-pressed=true]')?.scrollIntoView?.({ block: 'nearest', behavior: 'smooth' });
});
const index = computed(() => Math.max(0, props.floors.findIndex((f) => f.id === props.current)));
</script>

<template>
    <nav ref="nav" class="glass no-scrollbar relative max-h-full overflow-y-auto overscroll-contain rounded-3xl p-1.5" aria-label="Floors" data-testid="floor-selector">
        <!-- sliding highlight: springs between floors like a lift car -->
        <div
            class="absolute left-1.5 rounded-2xl bg-[#183c2d] transition-transform duration-500 motion-reduce:transition-none"
            :style="{ width: `${SIZE}px`, height: `${SIZE}px`, transform: `translateY(${index * (SIZE + GAP)}px)`, transitionTimingFunction: 'var(--ease-spring)' }"
        />
        <ul class="relative flex flex-col" :style="{ gap: `${GAP}px` }">
            <li v-for="f in floors" :key="f.id">
                <button
                    type="button"
                    class="relative flex items-center justify-center rounded-2xl text-base font-semibold transition-colors duration-300 active:scale-90"
                    :class="f.id === current ? 'text-white' : 'text-[#183c2d]'"
                    :style="{ width: `${SIZE}px`, height: `${SIZE}px` }"
                    :aria-pressed="f.id === current"
                    :aria-label="f.name"
                    @click="emit('select', f.id)"
                >
                    {{ f.label }}
                    <span v-if="routeFloors.has(f.id)" class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full" :class="f.id === current ? 'bg-[#b99a62]' : 'bg-[#14805e]'" />
                </button>
            </li>
        </ul>
    </nav>
</template>
