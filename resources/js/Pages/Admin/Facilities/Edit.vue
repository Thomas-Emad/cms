<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import type { Facility } from '@/types/facility';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  facility: Facility | null;
}>();

const isEdit = !!props.facility;

const form = useForm({
  name: props.facility?.name ?? '',
  slug: props.facility?.slug ?? '',
  description: props.facility?.description ?? '',
  short_description: props.facility?.short_description ?? '',
  category: props.facility?.category ?? 'other',
  building: props.facility?.building ?? '',
  floor: props.facility?.floor ?? '',
  wing: props.facility?.wing ?? '',
  phone: props.facility?.phone ?? '',
  email: props.facility?.email ?? '',
  status: props.facility?.status ?? 'draft',
});

const submit = () => {
  if (isEdit) {
    form.put(`/admin/facilities/${props.facility!.id}`);
  } else {
    form.post('/admin/facilities');
  }
};

const categories = ['wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other'];
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">
      {{ isEdit ? 'Edit Facility' : 'Add Facility' }}
    </h1>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
          <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Short description</label>
        <input v-model="form.short_description" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea v-model="form.description" rows="4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
          <select v-model="form.category" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Building</label>
          <input v-model="form.building" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Floor</label>
          <input v-model="form.floor" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50"
        >
          {{ isEdit ? 'Save changes' : 'Create facility' }}
        </button>
      </div>
    </form>
  </div>
</template>
