<script setup lang="ts">
import type { TextProps, SectionSettings, RenderMode } from '@/types/pageBuilder';

defineProps<{
    props: TextProps;
    settings: SectionSettings;
    mode: RenderMode;
}>();
</script>

<template>
    <section
        class="mx-auto max-w-7xl px-6 lg:px-10"
        :class="{
            'py-20 lg:py-28': settings.padding === 'large',
            'py-12 lg:py-16': settings.padding === 'medium' || !settings.padding,
            'py-6 lg:py-8': settings.padding === 'small',
            'py-0': settings.padding === 'none',
        }"
    >
        <!--
      Editorial split: heading sits in its own column on desktop so it
      reads as a statement rather than a stacked dashboard heading, with
      the body given room to breathe alongside it. Collapses to a single
      stacked column on mobile.
    -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12">
            <h2
                v-if="props.heading"
                class="reveal lg:col-span-5 text-3xl lg:text-4xl leading-tight"
                style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
                v-reveal
            >
                {{ props.heading }}
            </h2>
            <!--
        Plain text interpolation only - never v-html. `body` is stored
        and validated as plain text server-side (see TextSectionDefinition);
        white-space: pre-line preserves line breaks without parsing markup.
      -->
            <p
                class="reveal lg:col-span-6 lg:col-start-7 text-slate-600 text-base lg:text-lg leading-relaxed"
                style="white-space: pre-line"
                v-reveal="{ delay: 100 }"
            >
                {{ props.body }}
            </p>
        </div>
    </section>
</template>
