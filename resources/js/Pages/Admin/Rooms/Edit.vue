<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import TagListInput from '@/Components/Admin/TagListInput.vue';
import type { MediaItem, Room } from '@/types/room';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  room: Room | null;
  cover: MediaItem[];
  gallery: MediaItem[];
}>();

const isEdit = !!props.room;

const form = useForm({
  name: props.room?.name ?? '',
  slug: props.room?.slug ?? '',
  short_description: props.room?.short_description ?? '',
  description: props.room?.description ?? '',
  size_sqm: props.room?.size_sqm ?? null,
  max_guests: props.room?.max_guests ?? null,
  bed_type: props.room?.bed_type ?? '',
  view: props.room?.view ?? '',
  features: [...(props.room?.features ?? [])],
  status: props.room?.status ?? 'draft',
  featured: props.room?.featured ?? false,
  sort_order: props.room?.sort_order ?? 0,
});

const submit = () => {
  if (isEdit) form.put(`/admin/rooms/${props.room!.id}`);
  else form.post('/admin/rooms');
};

const input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm';
</script>

<template>
  <div class="max-w-3xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ isEdit ? 'Edit Room' : 'Add Room' }}</h1>
      <Link href="/admin/rooms" class="text-sm text-slate-500 hover:text-slate-800">← All rooms</Link>
    </div>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
          <input v-model="form.name" type="text" :class="input" placeholder="e.g. Deluxe Sea View Room" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Slug <span class="text-slate-400 font-normal">(optional)</span></label>
          <input v-model="form.slug" type="text" :class="input" placeholder="auto from name" />
          <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Short description</label>
        <input v-model="form.short_description" type="text" :class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea v-model="form.description" rows="4" :class="input" />
      </div>

      <div class="grid grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Size (m²)</label>
          <input v-model.number="form.size_sqm" type="number" min="1" :class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Max guests</label>
          <input v-model.number="form.max_guests" type="number" min="1" :class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Bed</label>
          <input v-model="form.bed_type" type="text" :class="input" placeholder="King" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">View</label>
          <input v-model="form.view" type="text" :class="input" placeholder="Sea" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Features</label>
        <TagListInput v-model="form.features" placeholder="e.g. Balcony — press Enter to add" />
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
          <select v-model="form.status" :class="input">
            <option value="draft">Draft (hidden)</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Order</label>
          <input v-model.number="form.sort_order" type="number" min="0" :class="input" />
        </div>
        <label class="flex items-end gap-2 pb-2 text-sm text-slate-700">
          <input v-model="form.featured" type="checkbox" /> Featured
        </label>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? 'Save changes' : 'Create room' }}
        </button>
      </div>
    </form>

    <div class="mt-6 space-y-6 rounded-lg border border-slate-200 bg-white p-6">
      <template v-if="isEdit">
        <MediaManager mediable-type="room" :mediable-id="room!.id" collection="cover" :items="cover" label="Main photo" hint="Shown on the room card and at the top of its page." />
        <MediaManager mediable-type="room" :mediable-id="room!.id" collection="gallery" :items="gallery" label="Gallery" hint="Guests can tap a photo to view it full screen." />
      </template>
      <p v-else class="text-sm text-slate-500">Save the room first, then you can add photos.</p>
    </div>
  </div>
</template>
