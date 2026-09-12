<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import type { Offer } from '@/types/content';
import type { Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{ offers: Paginated<Offer> }>();

const destroy = (offer: Offer) => {
  if (confirm(`Delete "${offer.title}"?`)) router.delete(`/admin/offers/${offer.id}`);
};
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">Offers</h1>
      <Link href="/admin/offers/create" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900">+ Add Offer</Link>
    </div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
          <tr><th class="px-4 py-2">Title</th><th class="px-4 py-2">Discount</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="offer in offers.data" :key="offer.id">
            <td class="px-4 py-2 font-medium text-slate-700">{{ offer.title }} <span v-if="offer.featured" class="ml-1 text-amber-500">★</span></td>
            <td class="px-4 py-2 text-slate-500">{{ offer.discount ? offer.discount + '%' : '—' }}</td>
            <td class="px-4 py-2">
              <span class="rounded-full px-2 py-0.5 text-xs" :class="offer.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">{{ offer.status }}</span>
            </td>
            <td class="px-4 py-2 text-right space-x-3">
              <Link :href="`/admin/offers/${offer.id}/edit`" class="text-slate-500 hover:text-slate-800">Edit</Link>
              <button class="text-red-500 hover:text-red-700" @click="destroy(offer)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
