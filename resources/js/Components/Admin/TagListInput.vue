<script setup lang="ts">
import { ref } from 'vue';

/** Editable list of short strings (features / amenities). v-model is string[]. */
const model = defineModel<string[]>({ default: () => [] });
defineProps<{ placeholder?: string }>();

const draft = ref('');

function add() {
    const value = draft.value.trim();
    if (!value || model.value.includes(value)) {
        draft.value = '';
        return;
    }
    model.value = [...model.value, value];
    draft.value = '';
}

function remove(index: number) {
    model.value = model.value.filter((_, i) => i !== index);
}
</script>

<template>
    <div>
        <ul v-if="model.length" class="mb-2 flex flex-wrap gap-2">
            <li v-for="(tag, i) in model" :key="tag" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">
                {{ tag }}
                <button type="button" class="text-slate-400 hover:text-red-600" :aria-label="`Remove ${tag}`" @click="remove(i)">×</button>
            </li>
        </ul>
        <div class="flex gap-2">
            <input
                v-model="draft"
                type="text"
                :placeholder="placeholder"
                class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm"
                @keydown.enter.prevent="add"
            />
            <button type="button" class="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="add">Add</button>
        </div>
    </div>
</template>
