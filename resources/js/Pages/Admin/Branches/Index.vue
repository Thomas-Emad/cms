<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { HotelBranch } from '@/types/branch';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

defineProps<{ branches: Paginated<HotelBranch> }>();

const { t } = useI18n();

const destroy = (branch: HotelBranch) => {
    router.delete(`/admin/branches/${branch.id}`);
};
</script>

<template>
    <div>
        <PageHeader
            :title="t('branches.admin_title', undefined, 'Hotel Branches')"
            :subtitle="t('branches.admin_subtitle', undefined, 'Manage hotel properties, regional branches, and location profiles.')"
        >
            <template #actions>
                <CreateButton href="/admin/branches/create">
                    {{ t('branches.create', undefined, '+ Add Branch') }}
                </CreateButton>
            </template>
        </PageHeader>

        <AdminTable
            :items="branches.data"
            :empty-message="t('branches.empty', undefined, 'No branches available at the moment.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 w-16"></th>
                    <th class="px-4 py-3 text-start">{{ t('common.name', undefined, 'Name') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('branches.location', undefined, 'City & Address') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('branches.contact', undefined, 'Contact') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="branch in branches.data"
                :key="branch.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-2.5">
                    <img
                        v-if="branch.cover_image_url"
                        :src="branch.cover_image_url"
                        alt=""
                        class="h-10 w-14 rounded object-cover shadow-2xs border border-slate-100"
                    />
                    <div v-else class="h-10 w-14 rounded bg-slate-100 border border-slate-200/50 flex items-center justify-center text-xs text-slate-400">
                        🏨
                    </div>
                </td>
                <td class="px-4 py-2.5 font-medium text-slate-800">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span>{{ branch.name }}</span>
                        <span v-if="branch.is_main" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            ★ {{ t('branches.main_branch', undefined, 'Main Branch') }}
                        </span>
                        <a
                            v-if="branch.domain"
                            :href="`http://${branch.domain}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors"
                            :title="`Open http://${branch.domain}`"
                        >
                            <span>🌐 {{ branch.domain }}</span>
                            <span class="text-indigo-400 text-[9px]">↗</span>
                        </a>
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5 font-mono">{{ branch.slug }}</div>
                </td>
                <td class="px-4 py-2.5 text-sm text-slate-600">
                    <div class="font-medium text-slate-700">{{ branch.city || '—' }}</div>
                    <div class="text-xs text-slate-400 line-clamp-1">{{ branch.address || '—' }}</div>
                </td>
                <td class="px-4 py-2.5 text-sm text-slate-600">
                    <div v-if="branch.phone" class="text-xs">{{ branch.phone }}</div>
                    <div v-if="branch.email" class="text-xs text-slate-400">{{ branch.email }}</div>
                    <div v-if="!branch.phone && !branch.email" class="text-xs text-slate-400">—</div>
                </td>
                <td class="px-4 py-2.5">
                    <AdminBadge :variant="branch.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${branch.status}`, undefined, branch.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-2.5 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/branches/${branch.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${branch.name}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(branch)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
