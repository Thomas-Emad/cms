<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Facility, Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  facilities: Paginated<Facility>;
  category?: string | null;
}>();

const { t } = useI18n();

// ?category=meeting is the "Meeting Rooms" view of the same list.
const isMeeting = props.category === 'meeting';
const title = isMeeting ? t('admin.facilities.meeting_rooms', 'Meeting Rooms') : t('admin.facilities.title', 'Facilities');
const createUrl = isMeeting ? '/admin/facilities/create?category=meeting' : '/admin/facilities/create';

const destroy = (facility: Facility) => {
  if (confirm(`${t('admin.common.delete_confirm', 'Delete')} "${facility.name}"? ${t('admin.common.cannot_be_undone', 'This cannot be undone.')}`)) {
    router.delete(`/admin/facilities/${facility.id}`);
  }
};
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ title }}</h1>
      <Link
        :href="createUrl"
        class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900"
      >
        {{ isMeeting ? t('admin.facilities.add_meeting_room', '+ Add Meeting Room') : t('admin.facilities.add_facility', '+ Add Facility') }}
      </Link>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-start text-xs uppercase text-slate-400">
          <tr>
            <th class="px-4 py-2 text-start">{{ $t('common.name') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('common.category') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('common.status') }}</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="facility in facilities.data" :key="facility.id">
            <td class="px-4 py-2 font-medium text-slate-700">{{ facility.name }}</td>
            <td class="px-4 py-2 text-slate-500">{{ $t(`admin.facility_categories.${facility.category}`, facility.category) }}</td>
            <td class="px-4 py-2">
              <span
                class="rounded-full px-2 py-0.5 text-xs"
                :class="facility.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
              >
                {{ $t(`admin.status.${facility.status}`, facility.status) }}
              </span>
            </td>
            <td class="px-4 py-2 text-end space-x-3 rtl:space-x-reverse">
              <Link :href="`/admin/facilities/${facility.id}/edit`" class="text-slate-500 hover:text-slate-800">
                {{ $t('common.edit') }}
              </Link>
              <button class="text-red-500 hover:text-red-700" @click="destroy(facility)">{{ $t('common.delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="!facilities.data.length" class="text-sm text-slate-400 mt-6 text-center">
      {{ $t('admin.common.empty', 'Nothing here yet — add your first one.') }}
    </p>
  </div>
</template>
