<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import type { Service } from '@/types/content';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ service: Service | null }>();
const isEdit = !!props.service;

const form = useForm({
  name: props.service?.name ?? '',
  slug: props.service?.slug ?? '',
  description: props.service?.description ?? '',
  icon: props.service?.icon ?? '',
  contact: props.service?.contact ?? '',
  price: props.service?.price ?? '',
  request_enabled: props.service?.request_enabled ?? false,
  status: props.service?.status ?? 'draft',
});

const submit = () => {
  if (isEdit) form.put(`/admin/services/${props.service!.id}`);
  else form.post('/admin/services');
};
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">{{ isEdit ? 'Edit Service' : 'Add Service' }}</h1>
    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
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
          <label class="block text-sm font-medium text-slate-700 mb-1">Price (blank = complimentary/on request)</label>
          <input v-model="form.price" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Contact</label>
          <input v-model="form.contact" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input v-model="form.request_enabled" type="checkbox" class="rounded border-slate-300" />
        Guests can submit a request for this service from the app
      </label>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
          <option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option>
        </select>
      </div>
      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? 'Save changes' : 'Create service' }}
        </button>
      </div>
    </form>
  </div>
</template>
