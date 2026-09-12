<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import type { Experience } from '@/types/content';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ experience: Experience | null }>();
const isEdit = !!props.experience;

const form = useForm({
  title: props.experience?.title ?? '',
  slug: props.experience?.slug ?? '',
  description: props.experience?.description ?? '',
  category: props.experience?.category ?? '',
  duration: props.experience?.duration ?? '',
  price: props.experience?.price ?? '',
  booking_url: props.experience?.booking_url ?? '',
  featured: props.experience?.featured ?? false,
  status: props.experience?.status ?? 'draft',
});

const submit = () => {
  if (isEdit) form.put(`/admin/experiences/${props.experience!.id}`);
  else form.post('/admin/experiences');
};
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">{{ isEdit ? 'Edit Experience' : 'Add Experience' }}</h1>
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
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
          <input v-model="form.category" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Duration</label>
          <input v-model="form.duration" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Price</label>
          <input v-model="form.price" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input v-model="form.featured" type="checkbox" class="rounded border-slate-300" />
        Featured
      </label>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
          <option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option>
        </select>
      </div>
      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? 'Save changes' : 'Create experience' }}
        </button>
      </div>
    </form>
  </div>
</template>
