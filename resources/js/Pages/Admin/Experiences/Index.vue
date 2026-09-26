<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Experience } from '@/types/content';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge, BranchFilter } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

defineProps<{
    experiences: Paginated<Experience>;
    selected_branch_id?: number | null;
}>();

const { t, locale } = useI18n();

const destroy = (experience: Experience) => {
    router.delete(`/admin/experiences/${experience.id}`);
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.experiences.title', undefined, 'Experiences & Tours') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('admin.experiences.subtitle', undefined, 'Curated guest excursions, desert safaris, and wellness journeys.') }}</p>
            </div>
            <CreateButton href="/admin/experiences/create">
                {{ t('admin.experiences.create', undefined, '+ Add Experience') }}
            </CreateButton>
        </div>

        <div class="mb-4">
            <BranchFilter :selected-branch-id="selected_branch_id" />
        </div>

        <AdminTable
            :items="experiences.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 text-start">{{ t('common.title', undefined, 'Title') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.branch', undefined, 'Branch') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.category', undefined, 'Category') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="experience in experiences.data"
                :key="experience.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-3 font-medium text-slate-800">
                    {{ experience.title }}
                    <span v-if="experience.featured" class="ms-1.5 text-xs text-amber-500">★</span>
                </td>
                <td class="px-4 py-3">
                    <AdminBadge v-if="(experience as any).branch" variant="info">
                        📍 {{ (experience as any).branch.name }}
                    </AdminBadge>
                    <span v-else class="text-xs text-slate-400">
                        {{ locale === 'ar' ? 'عام (كل الفروع)' : 'All Branches' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ experience.category || '—' }}</td>
                <td class="px-4 py-3">
                    <AdminBadge :variant="experience.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${experience.status}`, undefined, experience.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/experiences/${experience.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${experience.title}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(experience)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
