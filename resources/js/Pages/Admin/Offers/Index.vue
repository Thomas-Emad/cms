<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Offer } from '@/types/content';
import type { Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{ offers: Paginated<Offer> }>();

const { t } = useI18n();

const destroy = (offer: Offer) => {
  if (confirm(`${t('admin.common.delete_confirm', 'Delete')} "${offer.title}"? ${t('admin.common.cannot_be_undone', 'This cannot be undone.')}`)) {
    router.delete(`/admin/offers/${offer.id}`);
  }
};
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.offers.title', 'Offers') }}</h1>
      <Link href="/admin/offers/create" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900">
        {{ t('admin.offers.create', '+ Add Offer') }}
      </Link>
    </div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-start text-xs uppercase text-slate-400">
          <tr>
            <th class="px-4 py-2 text-start">{{ $t('common.title') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('admin.offers.form.discount', 'Discount') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('common.status') }}</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="offer in offers.data" :key="offer.id">
            <td class="px-4 py-2 font-medium text-slate-700">{{ offer.title }} <span v-if="offer.featured" class="ms-1 text-amber-500">★</span></td>
            <td class="px-4 py-2 text-slate-500">{{ offer.discount ? offer.discount + '%' : '—' }}</td>
            <td class="px-4 py-2">
              <span class="rounded-full px-2 py-0.5 text-xs" :class="offer.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                {{ $t(`admin.status.${offer.status}`, offer.status) }}
              </span>
            </td>
            <td class="px-4 py-2 text-end space-x-3 rtl:space-x-reverse">
              <Link :href="`/admin/offers/${offer.id}/edit`" class="text-slate-500 hover:text-slate-800">{{ $t('common.edit') }}</Link>
              <button class="text-red-500 hover:text-red-700" @click="destroy(offer)">{{ $t('common.delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="!offers.data.length" class="text-sm text-slate-400 mt-6 text-center">
      {{ $t('admin.common.empty', 'Nothing here yet — add your first one.') }}
    </p>
  </div>
</template>
