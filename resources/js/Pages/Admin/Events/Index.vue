<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { HotelEvent } from '@/types/content';
import type { Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{ events: Paginated<HotelEvent> }>();

const { t } = useI18n();

const destroy = (event: HotelEvent) => {
    if (confirm(`${t('admin.common.delete_confirm', 'Delete')} "${event.title}"? ${t('admin.common.cannot_be_undone', 'This cannot be undone.')}`)) {
        router.delete(`/admin/events/${event.id}`);
    }
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.events.title', 'Events') }}</h1>
            <Link href="/admin/events/create"
                class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900">
                {{ t('admin.events.create', '+ Add Event') }}
            </Link>
        </div>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-start text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-4 py-2 text-start">{{ $t('common.title') }}</th>
                        <th class="px-4 py-2 text-start">{{ $t('common.date') }}</th>
                        <th class="px-4 py-2 text-start">{{ $t('common.status_label') }}</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="event in events.data" :key="event.id">
                        <td class="px-4 py-2 font-medium text-slate-700">{{ event.title }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ event.start_date }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs"
                                :class="event.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                {{ $t(`admin.status.${event.status}`, event.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-end space-x-3 rtl:space-x-reverse">
                            <Link :href="`/admin/events/${event.id}/edit`" class="text-slate-500 hover:text-slate-800">
                                {{ $t('common.edit') }}</Link>
                            <button class="text-red-500 hover:text-red-700" @click="destroy(event)">{{
                                $t('common.delete') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-if="!events.data.length" class="text-sm text-slate-400 mt-6 text-center">
            {{ $t('admin.common.empty', 'Nothing here yet — add your first one.') }}
        </p>
    </div>
</template>
