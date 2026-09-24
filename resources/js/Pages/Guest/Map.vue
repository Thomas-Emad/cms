<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestShell from '@/Layouts/GuestShell.vue';
import MapExperience from '@/Map/MapExperience.vue';
import type { HotelMapData } from '@/Map/types';
import { useI18n } from '@/i18n';

defineOptions({ layout: GuestShell });

const props = defineProps<{ map: HotelMapData | null; place?: string | null }>();

const { t, locale } = useI18n();
const activeMode = ref<'interactive' | 'photo'>('interactive');
const zoomLevel = ref(1);

const usable = computed(() => !!props.map && props.map.floors?.length > 0 && props.map.nodes?.length > 0 && props.map.locations?.length > 0);

function zoomIn() {
    zoomLevel.value = Math.min(2.5, zoomLevel.value + 0.25);
}

function zoomOut() {
    zoomLevel.value = Math.max(0.75, zoomLevel.value - 0.25);
}

function resetZoom() {
    zoomLevel.value = 1;
}
</script>

<template>

    <Head :title="t('map.title', undefined, 'Resort Map & Directory')" />

    <div class="relative w-full h-[calc(100vh-var(--kiosk-topbar-h,5rem)-var(--kiosk-dock-h,7.5rem))] flex flex-col">
        <!-- Map Mode Switcher Top Overlay -->
        <div
            class="absolute top-4 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1.5 p-1 rounded-full bg-slate-900/85 backdrop-blur-md border border-white/20 shadow-xl text-xs sm:text-sm">
            <button type="button"
                class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-medium transition-all" :class="activeMode === 'interactive'
                    ? 'shadow-md text-white'
                    : 'text-white/70 hover:text-white hover:bg-white/10'"
                :style="activeMode === 'interactive' ? { backgroundColor: 'var(--color-primary, #059669)' } : {}"
                @click="activeMode = 'interactive'">
                <span>🗺️</span>
                <span>{{ locale === 'ar' ? 'الخريطة التفاعلية' : 'Interactive Map' }}</span>
            </button>

            <button type="button"
                class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full font-medium transition-all" :class="activeMode === 'photo'
                    ? 'shadow-md text-white'
                    : 'text-white/70 hover:text-white hover:bg-white/10'"
                :style="activeMode === 'photo' ? { backgroundColor: 'var(--color-primary, #059669)' } : {}"
                @click="activeMode = 'photo'">
                <span>🖼️</span>
                <span>{{ locale === 'ar' ? 'صورة خريطة المنتجع' : 'Resort Photo Map' }}</span>
            </button>
        </div>

        <!-- Mode 1: Interactive Map Experience -->
        <div v-show="activeMode === 'interactive'" class="flex-1 w-full h-full">
            <MapExperience v-if="usable" :data="map!" :place="place" />
            <div v-else class="mx-auto flex max-w-3xl flex-col items-center px-10 pt-40 pb-16 text-center"
                data-testid="map-empty">
                <p class="text-7xl" aria-hidden="true">🗺️</p>
                <h1 class="mt-6 text-4xl"
                    style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">
                    {{ t('map.empty_title') }}
                </h1>
                <p class="mt-3 text-xl text-slate-500">{{ t('map.empty_desc') }}</p>
            </div>
        </div>

        <!-- Mode 2: High-Resolution Resort Photo Map -->
        <div v-show="activeMode === 'photo'"
            class="relative flex-1 w-full h-full bg-slate-950 overflow-hidden flex items-center justify-center">
            <!-- Zoom Controls -->
            <div
                class="absolute bottom-6 right-6 z-30 flex items-center gap-2 bg-slate-900/90 backdrop-blur-md border border-white/20 p-1.5 rounded-full shadow-lg text-white">
                <button type="button"
                    class="h-8 w-8 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                    :title="locale === 'ar' ? 'تكبير' : 'Zoom In'" @click="zoomIn">
                    +
                </button>
                <button type="button"
                    class="h-8 w-8 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors text-xs font-mono"
                    :title="locale === 'ar' ? 'إعادة ضبط' : 'Reset'" @click="resetZoom">
                    {{ Math.round(zoomLevel * 100) }}%
                </button>
                <button type="button"
                    class="h-8 w-8 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                    :title="locale === 'ar' ? 'تصغير' : 'Zoom Out'" @click="zoomOut">
                    -
                </button>
                <div class="h-4 w-px bg-white/20" />
            </div>
        </div>
    </div>
</template>
