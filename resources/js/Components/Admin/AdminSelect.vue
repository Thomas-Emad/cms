<script setup lang="ts">
import { computed } from 'vue';

export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        label?: string;
        error?: string | null;
        hint?: string;
        options: Array<string | SelectOption>;
        placeholder?: string;
        required?: boolean;
        disabled?: boolean;
        id?: string;
        name?: string;
    }>(),
    {
        modelValue: '',
        label: undefined,
        error: undefined,
        hint: undefined,
        placeholder: undefined,
        required: false,
        disabled: false,
        id: undefined,
        name: undefined,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number | null): void;
}>();

const inputId = computed(() => props.id || (props.label ? `select-${props.label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-${Math.random().toString(36).substring(2, 7)}` : undefined));

const normalizedOptions = computed<SelectOption[]>(() => {
    return props.options.map((opt) => {
        if (typeof opt === 'object' && opt !== null) {
            return opt;
        }
        return { value: opt, label: String(opt) };
    });
});

function handleChange(event: Event) {
    const target = event.target as HTMLSelectElement;
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

        <select
            :id="inputId"
            :name="name"
            :value="modelValue"
            :required="required"
            :disabled="disabled"
            class="block w-full rounded-md border text-sm px-3 py-2 bg-white transition-colors duration-150 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed"
            :class="[
                error
                    ? 'border-rose-400 text-rose-900 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200'
                    : 'border-slate-300 text-slate-900 admin-theme-input'
            ]"
            @change="handleChange"
        >
            <option v-if="placeholder" value="" disabled :selected="modelValue === '' || modelValue === null">
                {{ placeholder }}
            </option>
            <option
                v-for="opt in normalizedOptions"
                :key="opt.value"
                :value="opt.value"
                :disabled="opt.disabled"
            >
                {{ opt.label }}
            </option>
        </select>

        <p v-if="error" class="mt-1.5 text-xs text-rose-600 font-medium">
            {{ error }}
        </p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-400">
            {{ hint }}
        </p>
    </div>
</template>
