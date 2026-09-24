<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        label?: string;
        error?: string | null;
        hint?: string;
        rows?: number;
        placeholder?: string;
        dir?: 'ltr' | 'rtl' | 'auto';
        required?: boolean;
        disabled?: boolean;
        readonly?: boolean;
        id?: string;
        name?: string;
    }>(),
    {
        modelValue: '',
        label: undefined,
        error: undefined,
        hint: undefined,
        rows: 3,
        placeholder: '',
        dir: 'auto',
        required: false,
        disabled: false,
        readonly: false,
        id: undefined,
        name: undefined,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const inputId = computed(() => props.id || (props.label ? `textarea-${props.label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-${Math.random().toString(36).substring(2, 7)}` : undefined));

function handleInput(event: Event) {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
}
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="inputId"
            class="block text-sm font-medium text-slate-700 mb-1"
        >
            {{ label }}
            <span v-if="required" class="text-rose-500 ms-0.5">*</span>
        </label>

        <textarea
            :id="inputId"
            :name="name"
            :rows="rows"
            :value="modelValue"
            :placeholder="placeholder"
            :dir="dir"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            class="block w-full rounded-md border text-sm px-3 py-2 transition-colors duration-150 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed"
            :class="[
                error
                    ? 'border-rose-400 text-rose-900 placeholder-rose-300 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200'
                    : 'border-slate-300 text-slate-900 placeholder-slate-400 admin-theme-input'
            ]"
            @input="handleInput"
        />

        <p v-if="error" class="mt-1.5 text-xs text-rose-600 font-medium">
            {{ error }}
        </p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-400">
            {{ hint }}
        </p>
    </div>
</template>
