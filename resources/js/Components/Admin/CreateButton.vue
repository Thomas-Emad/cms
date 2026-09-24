<script setup lang="ts">
import AdminButton from './AdminButton.vue';

withDefaults(
    defineProps<{
        href?: string;
        label?: string;
        size?: 'xs' | 'sm' | 'md' | 'lg';
        disabled?: boolean;
    }>(),
    {
        size: 'sm',
        disabled: false,
    }
);

defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();
</script>

<template>
    <AdminButton
        variant="primary"
        :size="size"
        :href="href"
        :disabled="disabled"
        @click="$emit('click', $event)"
    >
        <template #icon-start>
            <svg
                class="h-4 w-4 shrink-0 -ms-0.5"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"
                />
            </svg>
        </template>
        <slot>{{ label ?? 'Create' }}</slot>
    </AdminButton>
</template>
