<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        padding?: 'none' | 'sm' | 'md' | 'lg';
    }>(),
    {
        title: undefined,
        subtitle: undefined,
        padding: 'md',
    }
);

const paddingClass = computed(() => {
    switch (props.padding) {
        case 'none':
            return 'p-0';
        case 'sm':
            return 'p-4';
        case 'lg':
            return 'p-8';
        case 'md':
        default:
            return 'p-6';
    }
});
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white shadow-xs overflow-hidden">
        <div
            v-if="title || $slots.header || $slots.actions"
            class="flex items-center justify-between border-b border-slate-100 px-6 py-4"
        >
            <div>
                <slot name="header">
                    <h2 v-if="title" class="text-base font-semibold text-slate-800">{{ title }}</h2>
                    <p v-if="subtitle" class="mt-0.5 text-xs text-slate-500">{{ subtitle }}</p>
                </slot>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <div :class="paddingClass">
            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="border-t border-slate-100 bg-slate-50/50 px-6 py-3"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
