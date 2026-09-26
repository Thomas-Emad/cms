<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';

interface BranchItem {
    id: number;
    name: string;
    city?: string | null;
}

const props = defineProps<{
    selectedBranchId?: number | null;
    branches?: BranchItem[];
    baseUrl?: string;
    extraParams?: Record<string, any>;
}>();

const { locale } = useI18n();
const page = usePage();

const availableBranches = computed<BranchItem[]>(() => {
    if (props.branches && props.branches.length > 0) {
        return props.branches;
    }
    return ((page.props as any).branches as BranchItem[]) || [];
});

function filterByBranch(branchId: number | null) {
    const url = props.baseUrl || window.location.pathname;
    const params: Record<string, any> = {
        ...(props.extraParams || {}),
    };
    if (branchId) {
        params.branch_id = branchId;
    } else {
        delete params.branch_id;
    }
    router.get(url, params, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <div v-if="availableBranches.length > 0" class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <span class="text-slate-400 font-medium whitespace-nowrap mr-1 rtl:mr-0 rtl:ml-1">
            {{ locale === 'ar' ? 'تصفية حسب الفرع:' : 'Filter Branch:' }}
        </span>
        <button
            type="button"
            class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap font-medium"
            :class="!selectedBranchId ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="filterByBranch(null)"
        >
            {{ locale === 'ar' ? 'الكل' : 'All' }}
        </button>
        <button
            v-for="b in availableBranches"
            :key="b.id"
            type="button"
            class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap font-medium"
            :class="selectedBranchId === b.id ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="filterByBranch(b.id)"
        >
            📍 {{ b.name }}
        </button>
    </div>
</template>
