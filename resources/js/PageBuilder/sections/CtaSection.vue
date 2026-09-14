<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface CtaProps {
    heading?: string;
    subheading?: string;
    button_text?: string;
    button_url?: string;
}

defineProps<{
    props: CtaProps;
    settings: SectionSettings;
    mode: RenderMode;
}>();
</script>

<template>
    <section
        class="text-center px-6 py-20 lg:py-28"
        :style="{ background: settings.background === 'brand' ? 'var(--luxury-forest)' : 'var(--luxury-cream)' }"
    >
        <div class="mx-auto max-w-2xl">
            <h2
                class="reveal text-3xl lg:text-4xl leading-tight"
                :style="{
                    fontFamily: 'var(--font-display)',
                    color: settings.background === 'brand' ? 'white' : 'var(--luxury-forest)',
                }"
                v-reveal
            >
                {{ props.heading }}
            </h2>
            <p
                v-if="props.subheading"
                class="reveal mt-3 text-base lg:text-lg"
                :class="settings.background === 'brand' ? 'text-white/80' : 'text-stone-600'"
                v-reveal="{ delay: 100 }"
            >
                {{ props.subheading }}
            </p>
            <a
                v-if="props.button_text && props.button_url"
                :href="props.button_url"
                class="reveal group mt-8 inline-flex items-center gap-2 text-sm uppercase tracking-wide"
                :style="{ color: settings.background === 'brand' ? 'white' : 'var(--luxury-forest)' }"
                v-reveal="{ delay: 200 }"
            >
                <span class="border-b border-current/40 pb-1 group-hover:border-current transition-colors">
                    {{ props.button_text }}
                </span>
                <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
            </a>
        </div>
    </section>
</template>
