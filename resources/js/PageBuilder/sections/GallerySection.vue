<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface GalleryProps {
    title?: string;
    media_ids?: number[];
}
interface GalleryData {
    media?: { url: string; alt_text: string | null }[];
}

defineProps<{
    props: GalleryProps;
    settings: SectionSettings;
    data?: GalleryData;
    mode: RenderMode;
}>();
</script>

<template>
    <section class="py-14 lg:py-20">
        <h2
            v-if="props.title"
            class="reveal mx-auto max-w-7xl px-6 lg:px-10 text-2xl lg:text-3xl mb-6 lg:mb-8"
            style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
            v-reveal
        >
            {{ props.title }}
        </h2>

        <div v-if="data?.media?.length" class="px-6 lg:px-10">
            <!--
        Horizontal, editorial-width gallery on larger screens rather than
        a cramped uniform grid - alternating tile widths give it a
        magazine-spread feel. Falls back to a simple 2-col grid on
        mobile where horizontal scroll is awkward to discover.
      -->
            <div class="grid grid-cols-2 gap-3 lg:hidden">
                <div
                    v-for="(item, i) in data.media"
                    :key="i"
                    class="reveal aspect-[4/5] overflow-hidden"
                    v-reveal="{ delay: i * 60 }"
                >
                    <img :src="item.url" :alt="item.alt_text ?? ''" class="w-full h-full object-cover" />
                </div>
            </div>

            <div class="hidden lg:flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory [scrollbar-width:thin]">
                <div
                    v-for="(item, i) in data.media"
                    :key="i"
                    class="reveal group shrink-0 snap-start overflow-hidden"
                    :class="i % 3 === 1 ? 'w-[440px] aspect-[3/4]' : 'w-[560px] aspect-[16/10]'"
                    v-reveal="{ delay: i * 60 }"
                >
                    <img
                        :src="item.url"
                        :alt="item.alt_text ?? ''"
                        class="w-full h-full object-cover transition-transform duration-700 ease-[var(--ease-cinematic)] group-hover:scale-105"
                    />
                </div>
            </div>
        </div>
        <p v-else class="mx-auto max-w-7xl px-6 lg:px-10 text-xs text-slate-400">No images selected yet.</p>
    </section>
</template>
