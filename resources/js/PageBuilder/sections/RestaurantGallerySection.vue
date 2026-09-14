<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface Data {
    media: { url: string; alt_text: string | null }[];
}

defineProps<{
    props: { title?: string };
    settings: SectionSettings;
    data?: Data;
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
            <div class="grid grid-cols-2 gap-3 lg:hidden">
                <div v-for="(m, i) in data.media" :key="i" class="reveal aspect-[4/5] overflow-hidden" v-reveal="{ delay: i * 60 }">
                    <img :src="m.url" :alt="m.alt_text ?? ''" class="w-full h-full object-cover" />
                </div>
            </div>

            <div class="hidden lg:flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory [scrollbar-width:thin]">
                <div
                    v-for="(m, i) in data.media"
                    :key="i"
                    class="reveal group shrink-0 snap-start overflow-hidden"
                    :class="i % 3 === 1 ? 'w-[440px] aspect-[3/4]' : 'w-[560px] aspect-[16/10]'"
                    v-reveal="{ delay: i * 60 }"
                >
                    <img
                        :src="m.url"
                        :alt="m.alt_text ?? ''"
                        class="w-full h-full object-cover transition-transform duration-700 ease-[var(--ease-cinematic)] group-hover:scale-105"
                    />
                </div>
            </div>
        </div>
        <p v-else class="mx-auto max-w-7xl px-6 lg:px-10 text-xs text-slate-400">
            No gallery images yet — add some from the Restaurant editor.
        </p>
    </section>
</template>
