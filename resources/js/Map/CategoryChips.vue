<script setup lang="ts">
import type { CategoryGroup } from './categories';

defineProps<{ groups: CategoryGroup[]; active: string }>();
const emit = defineEmits<{ (e: 'select', id: string): void }>();
</script>

<template>
    <div class="no-scrollbar flex gap-3 overflow-x-auto py-1 pe-2" role="tablist" aria-label="Categories" data-testid="chips">
        <button
            v-for="g in groups"
            :key="g.id"
            type="button"
            role="tab"
            :aria-selected="g.id === active"
            class="shrink-0 h-10 px-5 rounded-full text-sm font-medium whitespace-nowrap transition-all duration-300 active:scale-95"
            :class="g.id === active ? 'bg-[#183c2d] text-white shadow-lg' : 'glass text-[#183c2d]'"
            :style="{ transitionTimingFunction: 'var(--ease-spring)' }"
            @click="emit('select', g.id)"
        >
            {{ $t('map.groups.' + g.id) || g.label }}
        </button>
    </div>
</template>
