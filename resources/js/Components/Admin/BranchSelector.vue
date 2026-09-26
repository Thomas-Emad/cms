<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import AdminSelect from './AdminSelect.vue';

interface BranchItem {
    id: number;
    name: string;
    city?: string | null;
}

const props = defineProps<{
    modelValue: number | null | undefined;
    branches?: BranchItem[];
    error?: string;
    label?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void;
}>();

const { locale } = useI18n();
const page = usePage();

const availableBranches = computed<BranchItem[]>(() => {
    if (props.branches && props.branches.length > 0) {
        return props.branches;
    }
    return ((page.props as any).branches as BranchItem[]) || [];
});

const stringValue = computed({
    get: () => (props.modelValue ? String(props.modelValue) : ''),
    set: (val: string) => {
        emit('update:modelValue', val ? parseInt(val, 10) : null);
    },
});

const branchOptions = computed(() => [
    {
        value: '',
        label: locale.value === 'ar' ? '🌐 كل الفروع (عام للفندق)' : '🌐 All Branches (Hotel-Wide)',
    },
    ...availableBranches.value.map(b => ({
        value: String(b.id),
        label: `📍 ${b.name}${b.city ? ` (${b.city})` : ''}`,
    })),
]);
</script>

<template>
    <div v-if="availableBranches.length > 0" class="space-y-1">
        <AdminSelect
            v-model="stringValue"
            :label="label || (locale === 'ar' ? 'نطاق الفرع' : 'Branch Assignment')"
            :options="branchOptions"
            :error="error"
        />
        <p class="text-xs text-slate-400">
            {{ locale === 'ar'
                ? 'حدد ما إذا كان هذا المحتوى متاحاً في كل الفروع أم يقتصر على فرع محدد فقط.'
                : 'Choose whether this content applies to all hotel branches or is exclusive to a specific branch.'
            }}
        </p>
    </div>
</template>
