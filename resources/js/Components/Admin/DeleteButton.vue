<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label?: string;
        title?: string;
        confirmMessage?: string;
        size?: 'xs' | 'sm' | 'md';
        iconOnly?: boolean;
        disabled?: boolean;
        loading?: boolean;
    }>(),
    {
        size: 'xs',
        iconOnly: false,
        disabled: false,
        loading: false,
    }
);

const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void;
    (e: 'confirm'): void;
}>();

const sizeClasses = computed(() => {
    if (props.iconOnly) {
        return props.size === 'xs' ? 'p-1 text-xs rounded' : 'p-1.5 text-sm rounded-md';
    }
    return props.size === 'xs' ? 'px-2 py-1 text-xs gap-1 rounded' : 'px-2.5 py-1.5 text-xs gap-1.5 rounded-md';
});

function handleClick(event: MouseEvent) {
    if (props.confirmMessage) {
        if (window.confirm(props.confirmMessage)) {
            emit('confirm');
            emit('click', event);
        }
    } else {
        emit('click', event);
    }
}
</script>

<template>
    <button
        type="button"
        :disabled="disabled || loading"
        :title="title ?? label ?? 'Delete'"
        class="inline-flex items-center justify-center font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition-colors select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 disabled:opacity-50 disabled:pointer-events-none"
        :class="sizeClasses"
        @click="handleClick"
    >
        <svg
            v-if="!loading"
            class="h-3.5 w-3.5 shrink-0"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path
                fill-rule="evenodd"
                d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z"
                clip-rule="evenodd"
            />
        </svg>
        <svg
            v-else
            class="animate-spin h-3.5 w-3.5 text-rose-600"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span v-if="!iconOnly">
            <slot>{{ label ?? 'Delete' }}</slot>
        </span>
    </button>
</template>
