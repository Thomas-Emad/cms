<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { HotelEvent } from '@/types/content';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge, BranchFilter } from '@/Components/Admin';
import { formatDate } from '@/lib/formatters';

defineOptions({ layout: AdminLayout });

defineProps<{
    events: Paginated<HotelEvent>;
    selected_branch_id?: number | null;
}>();

const { t, locale } = useI18n();

const destroy = (event: HotelEvent) => {
    router.delete(`/admin/events/${event.id}`);
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.events.title', undefined, 'Events & Activities') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('admin.events.subtitle', undefined, 'Scheduled live music, dinners, festivals, and activities.') }}</p>
            </div>
            <CreateButton href="/admin/events/create">
                {{ t('admin.events.create', undefined, '+ Add Event') }}
            </CreateButton>
        </div>

        <div class="mb-4">
            <BranchFilter :selected-branch-id="selected_branch_id" />
        </div>

        <AdminTable
            :items="events.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 text-start">{{ t('common.title', undefined, 'Title') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.branch', undefined, 'Branch') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.date', undefined, 'Date') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="event in events.data"
                :key="event.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-3 font-medium text-slate-800">{{ event.title }}</td>
                <td class="px-4 py-3">
                    <AdminBadge v-if="(event as any).branch" variant="info">
                        📍 {{ (event as any).branch.name }}
                    </AdminBadge>
                    <span v-else class="text-xs text-slate-400">
                        {{ locale === 'ar' ? 'عام (كل الفروع)' : 'All Branches' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ formatDate(event.start_date) || '—' }}</td>
                <td class="px-4 py-3">
                    <AdminBadge :variant="event.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${event.status}`, undefined, event.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/events/${event.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${event.title}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(event)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
