<script setup lang="ts">
import { computed, ref } from 'vue';
import { CATEGORY_META } from './categories';
import type { MapFloor, MapLocation } from './types';

const query = defineModel<string>({ default: '' });
const props = defineProps<{
    results: MapLocation[];
    suggestions: MapLocation[];
    floors: MapFloor[];
    placeholder?: string;
}>();
const emit = defineEmits<{ (e: 'select', id: string): void }>();

const focused = ref(false);
const list = computed(() => (query.value.trim() ? props.results : props.suggestions));
const open = computed(() => focused.value && (list.value.length > 0 || query.value.trim().length > 0));
const floorName = (id: string) => props.floors.find((f) => f.id === id)?.name ?? '';

function choose(id: string) {
    emit('select', id);
    query.value = '';
    focused.value = false;
    (document.activeElement as HTMLElement | null)?.blur?.();
}
</script>

<template>
    <div class="glass rounded-3xl transition-shadow duration-300" :class="{ 'shadow-2xl': focused }" data-testid="search">
        <label class="flex h-12 items-center gap-3 px-4">
            <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 text-[#183c2d]" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
            </svg>
            <input
                v-model="query"
                type="search"
                class="min-w-0 flex-1 bg-transparent text-lg text-slate-900 outline-none placeholder:text-slate-400"
                :placeholder="placeholder ?? $t('map.search_placeholder')"
                :aria-label="$t('map.search_label')"
                autocomplete="off"
                @focus="focused = true"
                @blur="focused = false"
            />
            <button v-if="query" type="button" class="h-9 w-9 rounded-full bg-black/5 text-lg text-slate-500 active:scale-90" :aria-label="$t('common.clear')" @pointerdown.prevent @click="query = ''">×</button>
        </label>

        <!-- results: expand smoothly beneath the field -->
        <div class="expander" :class="{ 'expander--open': open }">
            <div>
                <ul class="max-h-[min(320px,45vh)] overflow-y-auto overscroll-contain border-t border-black/5 px-2 py-2" role="listbox">
                    <li v-if="!list.length" class="px-4 py-6 text-center text-slate-400">{{ $t('map.no_results') }}</li>
                    <li v-for="(loc, i) in list" :key="loc.id" role="option">
                        <button
                            type="button"
                            class="search-row flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-start active:bg-black/5"
                            :style="{ animationDelay: `${i * 25}ms` }"
                            @pointerdown.prevent
                            @click="choose(loc.id)"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-base text-white" :style="{ background: CATEGORY_META[loc.category].color }">{{ CATEGORY_META[loc.category].icon }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-base font-medium text-slate-900">{{ loc.name }}</span>
                                <span class="block text-sm text-slate-500">{{ $t('map.categories.' + loc.category) || CATEGORY_META[loc.category].label }} · {{ floorName(loc.floor) }}</span>
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-row {
    animation: search-in 320ms var(--ease-cinematic) both;
}
@keyframes search-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
    .search-row { animation: none; }
}
</style>
