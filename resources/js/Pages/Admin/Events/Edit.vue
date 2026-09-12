<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import type { HotelEvent } from '@/types/content';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ event: HotelEvent | null }>();
const isEdit = !!props.event;

const form = useForm({
  title: props.event?.title ?? '',
  slug: props.event?.slug ?? '',
  description: props.event?.description ?? '',
  start_date: props.event?.start_date ?? '',
  end_date: props.event?.end_date ?? '',
  start_time: props.event?.start_time ?? '',
  location: props.event?.location ?? '',
  capacity: props.event?.capacity ?? '',
  booking_required: props.event?.booking_required ?? false,
  booking_url: props.event?.booking_url ?? '',
  status: props.event?.status ?? 'draft',
});

const submit = () => {
  if (isEdit) form.put(`/admin/events/${props.event!.id}`);
  else form.post('/admin/events');
};
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">{{ isEdit ? 'Edit Event' : 'Add Event' }}</h1>
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
          <label class="block text-sm font-medium text-slate-700 mb-1">Start date</label>
          <input v-model="form.start_date" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">End date</label>
          <input v-model="form.end_date" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Start time</label>
          <input v-model="form.start_time" type="time" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
          <input v-model="form.location" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Capacity</label>
          <input v-model="form.capacity" type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-600">
        <input v-model="form.booking_required" type="checkbox" class="rounded border-slate-300" />
        Booking required
      </label>
      <div v-if="form.booking_required">
        <label class="block text-sm font-medium text-slate-700 mb-1">Booking URL</label>
        <input v-model="form.booking_url" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        <p v-if="form.errors.booking_url" class="mt-1 text-xs text-red-600">{{ form.errors.booking_url }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
          <option value="draft">Draft</option><option value="published">Published</option>
          <option value="cancelled">Cancelled</option><option value="archived">Archived</option>
        </select>
      </div>
      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? 'Save changes' : 'Create event' }}
        </button>
      </div>
    </form>
  </div>
</template>
