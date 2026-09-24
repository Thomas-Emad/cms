<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        items?: unknown[];
        empty?: boolean;
        emptyMessage?: string;
        emptyDescription?: string;
        striped?: boolean;
        hoverable?: boolean;
    }>(),
    {
        items: undefined,
        empty: undefined,
        emptyMessage: 'Nothing here yet — add your first one.',
        emptyDescription: '',
        striped: false,
        hoverable: true,
    }
);

const isEmpty = computed(() => {
    if (typeof props.empty === 'boolean') {
        return props.empty;
    }
    if (Array.isArray(props.items)) {
        return props.items.length === 0;
    }
    return false;
});
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-700">
                <thead v-if="$slots.header" class="border-b border-slate-200 bg-slate-50/80 text-xs font-semibold uppercase text-slate-500">
                    <slot name="header" />
                </thead>
                <tbody class="divide-y divide-slate-100" :class="{ 'divide-y-0': isEmpty }">
                    <slot v-if="!isEmpty" />
                    <tr v-else>
                        <td colspan="100%" class="py-12 px-4 text-center">
                            <slot name="empty">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" />
                                            <path d="M3 9h18" />
                                            <path d="M9 21V9" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">{{ emptyMessage }}</p>
                                    <p v-if="emptyDescription" class="mt-1 text-xs text-slate-400 max-w-sm">{{ emptyDescription }}</p>
                                    <div v-if="$slots['empty-action']" class="mt-4">
                                        <slot name="empty-action" />
                                    </div>
                                </div>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="$slots.footer || $slots.pagination" class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
            <slot name="footer" />
            <slot name="pagination" />
        </div>
    </div>
</template>
