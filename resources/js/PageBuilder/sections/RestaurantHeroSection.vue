<script setup lang="ts">
import { ref } from 'vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import { useParallax, vRevealMount } from '@/lib/motion';

interface Data {
    restaurant: {
        name: string;
        subtitle?: string;
        cover_image_url?: string | null;
        button_text?: string;
        reservation_url?: string | null;
    } | null;
}

const props = defineProps<{
    props: { subtitle_override?: string; button_text?: string };
    settings: SectionSettings;
    data?: Data;
    mode: RenderMode;
}>();

const heroRef = ref<HTMLElement | null>(null);
const { style: parallaxStyle } = useParallax(heroRef, { strength: 0.12 });
const isEditorCanvas = props.mode === 'edit';
</script>

<template>
    <section
        ref="heroRef"
        class="relative w-full overflow-hidden flex items-end"
        :class="isEditorCanvas ? 'h-[380px]' : 'h-[85vh] min-h-[520px]'"
        style="background: #1c1f26"
    >
        <div class="absolute inset-0">
            <img
                v-if="data?.restaurant?.cover_image_url"
                :src="data.restaurant.cover_image_url"
                :alt="data.restaurant.name"
                :class="isEditorCanvas ? 'w-full h-full object-cover' : 'reveal-scale w-full h-full object-cover'"
                :style="isEditorCanvas ? {} : parallaxStyle()"
                v-reveal-mount="isEditorCanvas ? undefined : { delay: 0 }"
            />
            <div
                class="absolute inset-0"
                :class="isEditorCanvas ? '' : 'reveal'"
                :style="{ background: 'var(--atmosphere-gradient)' }"
                v-reveal-mount="isEditorCanvas ? undefined : { delay: 150 }"
            />
        </div>

        <div v-if="data?.restaurant" class="relative z-10 w-full px-6 lg:px-10 pb-14 lg:pb-20">
            <div class="mx-auto max-w-7xl">
                <div
                    :class="isEditorCanvas ? '' : 'reveal-mask'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 300 }"
                >
                    <h1
                        class="text-white leading-[1.05]"
                        :class="[isEditorCanvas ? 'text-3xl' : 'reveal-mask-inner text-5xl lg:text-6xl']"
                        style="font-family: var(--font-display)"
                    >
                        {{ data.restaurant.name }}
                    </h1>
                </div>
                <p
                    v-if="props.subtitle_override ?? data.restaurant.subtitle"
                    class="mt-3 text-white/85 max-w-md"
                    :class="isEditorCanvas ? 'text-sm' : 'reveal text-lg'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 500 }"
                >
                    {{ props.subtitle_override ?? data.restaurant.subtitle }}
                </p>
                <a
                    v-if="data.restaurant.reservation_url"
                    :href="data.restaurant.reservation_url"
                    class="group mt-7 inline-flex items-center gap-2 text-white text-sm uppercase tracking-wide border-b border-white/40 pb-1 hover:border-white transition-colors"
                    :class="isEditorCanvas ? '' : 'reveal'"
                    v-reveal-mount="isEditorCanvas ? undefined : { delay: 650 }"
                >
                    {{ props.button_text ?? data.restaurant.button_text ?? 'Reserve a Table' }}
                    <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
                </a>
            </div>
        </div>
        <p v-else-if="mode === 'edit'" class="relative z-10 text-xs text-white/60 px-6 pb-6">No restaurant context available.</p>
    </section>
</template>
