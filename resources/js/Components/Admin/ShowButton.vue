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
        target?: string;
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
        :is="href ? (target ? 'a' : Link) : 'button'"
        :href="href"
        :target="target"
        :type="href ? undefined : 'button'"
        :disabled="disabled"
        :title="title ?? label ?? 'View'"
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
            <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
            <path
                fill-rule="evenodd"
                d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                clip-rule="evenodd"
            />
        </svg>
        <span v-if="!iconOnly">
            <slot>{{ label ?? 'View' }}</slot>
        </span>
    </component>
</template>
