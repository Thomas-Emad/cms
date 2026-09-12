<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import type { Offer } from '@/types/content';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ offer: Offer | null }>();
const isEdit = !!props.offer;

const form = useForm({
  title: props.offer?.title ?? '',
  slug: props.offer?.slug ?? '',
  description: props.offer?.description ?? '',
  price: props.offer?.price ?? '',
  discount: props.offer?.discount ?? '',
  valid_from: props.offer?.valid_from ?? '',
  valid_until: props.offer?.valid_until ?? '',
  booking_url: props.offer?.booking_url ?? '',
  featured: props.offer?.featured ?? false,
  status: props.offer?.status ?? 'draft',
});

const submit = () => {
  if (isEdit) form.put(`/admin/offers/${props.offer!.id}`);
  else form.post('/admin/offers');
};
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">{{ isEdit ? 'Edit Offer' : 'Add Offer' }}</h1>
    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
          <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
          <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Price</label>
          <input v-model="form.price" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Discount %</label>
          <input v-model="form.discount" type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Valid from</label>
          <input v-model="form.valid_from" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Valid until</label>
          <input v-model="form.valid_until" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.valid_until" class="mt-1 text-xs text-red-600">{{ form.errors.valid_until }}</p>
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input v-model="form.featured" type="checkbox" class="rounded border-slate-300" />
        Featured (eligible for homepage offer sections)
      </label>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
          <option value="draft">Draft</option><option value="published">Published</option>
          <option value="expired">Expired</option><option value="archived">Archived</option>
        </select>
      </div>
      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? 'Save changes' : 'Create offer' }}
        </button>
      </div>
    </form>
  </div>
</template>
