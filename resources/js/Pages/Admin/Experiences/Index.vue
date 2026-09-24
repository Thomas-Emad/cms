<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Experience } from '@/types/content';
import type { Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{ experiences: Paginated<Experience> }>();

const { t } = useI18n();

const destroy = (experience: Experience) => {
    if (confirm(`${t('admin.common.delete_confirm', 'Delete')} "${experience.title}"? ${t('admin.common.cannot_be_undone', 'This cannot be undone.')}`)) {
        router.delete(`/admin/experiences/${experience.id}`);
    }
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.experiences.title', 'Experiences') }}</h1>
            <Link href="/admin/experiences/create"
                class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900">
                {{ t('admin.experiences.create', '+ Add Experience') }}
            </Link>
        </div>
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-start text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-4 py-2 text-start">{{ $t('common.title') }}</th>
                        <th class="px-4 py-2 text-start">{{ $t('common.category') }}</th>
                        <th class="px-4 py-2 text-start">{{ $t('common.status_label') }}</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="experience in experiences.data" :key="experience.id">
                        <td class="px-4 py-2 font-medium text-slate-700">{{ experience.title }} <span
                                v-if="experience.featured" class="ms-1 text-amber-500">★</span></td>
                        <td class="px-4 py-2 text-slate-500">{{ experience.category }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs"
                                :class="experience.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                {{ $t(`admin.status.${experience.status}`, experience.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-end space-x-3 rtl:space-x-reverse">
                            <Link :href="`/admin/experiences/${experience.id}/edit`"
                                class="text-slate-500 hover:text-slate-800">{{ $t('common.edit') }}</Link>
                            <button class="text-red-500 hover:text-red-700" @click="destroy(experience)">{{
                                $t('common.delete') }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-if="!experiences.data.length" class="text-sm text-slate-400 mt-6 text-center">
            {{ $t('admin.common.empty', 'Nothing here yet — add your first one.') }}
        </p>
    </div>
</template>
