<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = withDefaults(
    defineProps<{
        href?: string;
        label?: string;
        title?: string;
        size?: 'xs' | 'sm' | 'md';
        iconOnly?: boolean;
        disabled?: boolean;
    }>(),
    {
        size: 'xs',
        iconOnly: false,
        disabled: false,
    }
);

defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();

const sizeClasses = computed(() => {
    if (props.iconOnly) {
        return props.size === 'xs' ? 'p-1 text-xs rounded' : 'p-1.5 text-sm rounded-md';
    }
    return props.size === 'xs' ? 'px-2 py-1 text-xs gap-1 rounded' : 'px-2.5 py-1.5 text-xs gap-1.5 rounded-md';
});
</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        :type="href ? undefined : 'button'"
        :disabled="disabled"
        :title="title ?? label ?? 'Edit'"
        class="inline-flex items-center justify-center font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 disabled:opacity-50 disabled:pointer-events-none"
        :class="sizeClasses"
        @click="$emit('click', $event)"
    >
        <svg
            class="h-3.5 w-3.5 shrink-0"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path
                d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"
            />
            <path
                d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"
            />
        </svg>
        <span v-if="!iconOnly">
            <slot>{{ label ?? 'Edit' }}</slot>
        </span>
    </component>
</template>
