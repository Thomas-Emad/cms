<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Facility, Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    facilities: Paginated<Facility>;
    category?: string | null;
}>();

const { t } = useI18n();

// ?category=meeting is the "Meeting Rooms" view of the same list.
const isMeeting = props.category === 'meeting';
const title = isMeeting ? t('admin.facilities.meeting_rooms', undefined, 'Meeting Rooms') : t('admin.facilities.title', undefined, 'Facilities');
const createUrl = isMeeting ? '/admin/facilities/create?category=meeting' : '/admin/facilities/create';

const destroy = (facility: Facility) => {
    router.delete(`/admin/facilities/${facility.id}`);
};
</script>

<template>
    <div>
        <PageHeader
            :title="title"
            :subtitle="isMeeting ? t('admin.facilities.meeting_desc', undefined, 'Manage your dedicated event & conference spaces.') : t('admin.facilities.desc', undefined, 'Manage guest amenities, pools, spas, and facilities.')"
        >
            <template #actions>
                <CreateButton :href="createUrl">
                    {{ isMeeting ? t('admin.facilities.add_meeting_room', undefined, '+ Add Meeting Room') : t('admin.facilities.add_facility', undefined, '+ Add Facility') }}
                </CreateButton>
            </template>
        </PageHeader>

        <AdminTable
            :items="facilities.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 text-start">{{ t('common.name', undefined, 'Name') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.category', undefined, 'Category') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="facility in facilities.data"
                :key="facility.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-3 font-medium text-slate-800">{{ facility.name }}</td>
                <td class="px-4 py-3 text-slate-500">
                    {{ t(`admin.facility_categories.${facility.category}`, undefined, facility.category) }}
                </td>
                <td class="px-4 py-3">
                    <AdminBadge :variant="facility.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${facility.status}`, undefined, facility.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/facilities/${facility.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${facility.name}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(facility)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
