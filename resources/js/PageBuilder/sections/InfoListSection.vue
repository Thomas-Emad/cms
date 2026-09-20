<script setup lang="ts">
import { computed } from 'vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface InfoListProps {
    title?: string;
    description?: string | null;
    items_text?: string | null;
}

const props = defineProps<{
    props: InfoListProps;
    settings: SectionSettings;
    mode: RenderMode;
}>();

type Row = { kind: 'group'; text: string } | { kind: 'item'; label: string; value: string };

/**
 * One row per line. "Label | Value" = a row; a line without "|" = a group
 * heading. Only the FIRST "|" splits, so values may contain "|".
 * Plain text interpolation only - never v-html.
 */
const rows = computed<Row[]>(() =>
    (props.props.items_text ?? '')
        .split('\n')
        .map((l) => l.trim())
        .filter(Boolean)
        .map((line): Row => {
            const at = line.indexOf('|');
            if (at === -1) return { kind: 'group', text: line };
            return { kind: 'item', label: line.slice(0, at).trim(), value: line.slice(at + 1).trim() };
        }),
);
</script>

<template>
    <section
        class="mx-auto max-w-5xl px-10"
        :class="{
            'py-20': settings.padding === 'large',
            'py-12': settings.padding === 'medium' || !settings.padding,
            'py-6': settings.padding === 'small',
            'py-0': settings.padding === 'none',
        }"
    >
        <h2
            v-if="props.props.title"
            class="reveal text-5xl leading-tight"
            style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)"
            v-reveal
        >
            {{ props.props.title }}
        </h2>
        <p v-if="props.props.description" class="reveal mt-3 text-xl text-slate-500 max-w-2xl" v-reveal="{ delay: 80 }">
            {{ props.props.description }}
        </p>

        <div class="mt-8">
            <template v-for="(row, i) in rows" :key="i">
                <h3
                    v-if="row.kind === 'group'"
                    class="reveal mt-10 first:mt-0 mb-2 text-sm uppercase tracking-[0.2em]"
                    style="color: var(--luxury-champagne)"
                    v-reveal
                >
                    {{ row.text }}
                </h3>
                <!-- Dotted leader between label and value, like the restaurant menu -->
                <div
                    v-else
                    class="reveal flex items-baseline gap-4 py-4 border-b border-slate-100 text-2xl"
                    v-reveal="{ delay: Math.min(i, 8) * 40 }"
                >
                    <span class="text-slate-800">{{ row.label }}</span>
                    <span class="flex-1 border-b border-dotted border-slate-300 translate-y-[-0.3em]" aria-hidden="true" />
                    <span class="tabular-nums text-slate-600 text-right">{{ row.value }}</span>
                </div>
            </template>
            <p v-if="!rows.length && props.mode === 'edit'" class="text-sm text-slate-400">
                No rows yet. One per line: Label | Value
            </p>
        </div>
    </section>
</template>
