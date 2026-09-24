<script setup lang="ts">
import { computed } from 'vue';

export type BadgeVariant =
    | 'published'
    | 'draft'
    | 'archived'
    | 'active'
    | 'inactive'
    | 'success'
    | 'warning'
    | 'danger'
    | 'info'
    | 'neutral';

const props = withDefaults(
    defineProps<{
        variant?: BadgeVariant | string;
        size?: 'sm' | 'md';
        dot?: boolean;
    }>(),
    {
        variant: 'neutral',
        size: 'sm',
        dot: false,
    }
);

const classes = computed(() => {
    switch (props.variant) {
        case 'published':
        case 'active':
        case 'success':
            return {
                badge: 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                dot: 'bg-emerald-500',
            };
        case 'draft':
        case 'warning':
            return {
                badge: 'bg-amber-50 text-amber-700 border-amber-200/60',
                dot: 'bg-amber-500',
            };
        case 'archived':
        case 'inactive':
        case 'neutral':
            return {
                badge: 'bg-slate-100 text-slate-600 border-slate-200/60',
                dot: 'bg-slate-400',
            };
        case 'danger':
            return {
                badge: 'bg-rose-50 text-rose-700 border-rose-200/60',
                dot: 'bg-rose-500',
            };
        case 'info':
            return {
                badge: 'bg-blue-50 text-blue-700 border-blue-200/60',
                dot: 'bg-blue-500',
            };
        default:
            return {
                badge: 'bg-slate-100 text-slate-600 border-slate-200/60',
                dot: 'bg-slate-400',
            };
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full border font-medium select-none"
        :class="[
            classes.badge,
            size === 'md' ? 'px-2.5 py-1 text-xs' : 'px-2 py-0.5 text-[11px]'
        ]"
    >
        <span
            v-if="dot"
            class="h-1.5 w-1.5 rounded-full"
            :class="classes.dot"
            aria-hidden="true"
        />
        <slot />
    </span>
</template>
