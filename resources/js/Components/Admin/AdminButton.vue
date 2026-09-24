<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

export type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'success' | 'ghost' | 'outline';
export type ButtonSize = 'xs' | 'sm' | 'md' | 'lg';

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariant;
        size?: ButtonSize;
        href?: string;
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        loading?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        type: 'button',
        disabled: false,
        loading: false,
    }
);

defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs':
            return 'px-2 py-1 text-xs gap-1 rounded';
        case 'sm':
            return 'px-3 py-1.5 text-sm gap-1.5 rounded-md';
        case 'lg':
            return 'px-5 py-2.5 text-base gap-2 rounded-lg';
        case 'md':
        default:
            return 'px-4 py-2 text-sm gap-2 rounded-md';
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'admin-theme-btn-primary border border-transparent shadow-xs';
        case 'secondary':
            return 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 shadow-xs focus-visible:ring-slate-300';
        case 'danger':
            return 'bg-rose-600 text-white hover:bg-rose-700 active:bg-rose-800 shadow-xs focus-visible:ring-rose-500';
        case 'success':
            return 'bg-emerald-600 text-white hover:bg-emerald-700 active:bg-emerald-800 shadow-xs focus-visible:ring-emerald-500';
        case 'outline':
            return 'bg-transparent text-slate-700 border border-slate-300 hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100';
        case 'ghost':
            return 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200';
        default:
            return 'admin-theme-btn-primary';
    }
});
</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        :type="href ? undefined : type"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center font-medium transition-all duration-150 select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none"
        :class="[sizeClasses, variantClasses]"
        @click="$emit('click', $event)"
    >
        <svg
            v-if="loading"
            class="animate-spin -ms-0.5 me-1.5 h-4 w-4 text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <slot name="icon-start" />
        <slot />
        <slot name="icon-end" />
    </component>
</template>
