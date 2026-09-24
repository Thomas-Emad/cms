<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Offer } from '@/types/content';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

defineProps<{ offers: Paginated<Offer> }>();

const { t } = useI18n();

const destroy = (offer: Offer) => {
    router.delete(`/admin/offers/${offer.id}`);
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.offers.title', undefined, 'Offers') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('admin.offers.subtitle', undefined, 'Special promotions, seasonal discounts, and guest packages.') }}</p>
            </div>
            <CreateButton href="/admin/offers/create">
                {{ t('admin.offers.create', undefined, '+ Add Offer') }}
            </CreateButton>
        </div>

        <AdminTable
            :items="offers.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 text-start">{{ t('common.title', undefined, 'Title') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.offers.form.discount', undefined, 'Discount') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="offer in offers.data"
                :key="offer.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-3 font-medium text-slate-800">
                    {{ offer.title }}
                    <span v-if="offer.featured" class="ms-1.5 text-xs text-amber-500">★</span>
                </td>
                <td class="px-4 py-3 text-slate-500 font-mono text-xs">
                    {{ offer.discount ? offer.discount + '%' : '—' }}
                </td>
                <td class="px-4 py-3">
                    <AdminBadge :variant="offer.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${offer.status}`, undefined, offer.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/offers/${offer.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${offer.title}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(offer)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
