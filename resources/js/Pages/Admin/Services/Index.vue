<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Service } from '@/types/content';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge, BranchFilter } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

defineProps<{
    services: Paginated<Service>;
    selected_branch_id?: number | null;
}>();

const { t, locale } = useI18n();

const destroy = (service: Service) => {
    router.delete(`/admin/services/${service.id}`);
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.services.title', undefined, 'Services') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('admin.services.subtitle', undefined, 'Manage guest services, concierge requests, and room amenities.') }}</p>
            </div>
            <CreateButton href="/admin/services/create">
                {{ t('admin.services.create', undefined, '+ Add Service') }}
            </CreateButton>
        </div>

        <div class="mb-4">
            <BranchFilter :selected-branch-id="selected_branch_id" />
        </div>

        <AdminTable
            :items="services.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 text-start">{{ t('common.name', undefined, 'Name') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.branch', undefined, 'Branch') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="service in services.data"
                :key="service.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-3 font-medium text-slate-800">{{ service.name }}</td>
                <td class="px-4 py-3">
                    <AdminBadge v-if="(service as any).branch" variant="info">
                        📍 {{ (service as any).branch.name }}
                    </AdminBadge>
                    <span v-else class="text-xs text-slate-400">
                        {{ locale === 'ar' ? 'عام (كل الفروع)' : 'All Branches' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <AdminBadge :variant="service.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${service.status}`, undefined, service.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/services/${service.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${service.name}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(service)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
