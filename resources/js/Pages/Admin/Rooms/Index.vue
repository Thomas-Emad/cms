<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Room } from '@/types/room';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge, PageHeader, BranchFilter } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

defineProps<{
    rooms: Paginated<Room>;
    selected_branch_id?: number | null;
}>();

const { t, locale } = useI18n();

const destroy = (room: Room) => {
    router.delete(`/admin/rooms/${room.id}`);
};
</script>

<template>
    <div>
        <PageHeader
            :title="t('admin.rooms.title', undefined, 'Rooms & Suites')"
            :subtitle="t('admin.rooms.subtitle', undefined, 'Manage hotel accommodations, suites, and room specs.')"
        >
            <template #actions>
                <CreateButton href="/admin/rooms/create">
                    {{ t('admin.rooms.create', undefined, '+ Add Room') }}
                </CreateButton>
            </template>
        </PageHeader>

        <div class="mb-4">
            <BranchFilter :selected-branch-id="selected_branch_id" />
        </div>

        <AdminTable
            :items="rooms.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 w-16"></th>
                    <th class="px-4 py-3 text-start">{{ t('common.name', undefined, 'Name') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.branch', undefined, 'Branch') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="room in rooms.data"
                :key="room.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-2.5">
                    <img
                        v-if="room.cover_image_url"
                        :src="room.cover_image_url"
                        alt=""
                        class="h-10 w-14 rounded object-cover shadow-2xs border border-slate-100"
                    />
                    <div v-else class="h-10 w-14 rounded bg-slate-100 border border-slate-200/50" />
                </td>
                <td class="px-4 py-2.5 font-medium text-slate-800">
                    {{ room.name }}
                    <span v-if="room.featured" class="ms-1.5 text-xs text-amber-500 font-normal">★ Featured</span>
                </td>
                <td class="px-4 py-2.5">
                    <AdminBadge v-if="(room as any).branch" variant="info">
                        📍 {{ (room as any).branch.name }}
                    </AdminBadge>
                    <span v-else class="text-xs text-slate-400">
                        {{ locale === 'ar' ? 'عام (كل الفروع)' : 'All Branches' }}
                    </span>
                </td>
                <td class="px-4 py-2.5">
                    <AdminBadge :variant="room.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${room.status}`, undefined, room.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-2.5 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/rooms/${room.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${room.name}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(room)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
