<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import TagListInput from '@/Components/Admin/TagListInput.vue';
import type { MediaItem, Room } from '@/types/room';
import { AdminCard, AdminInput, AdminTextarea, AdminSelect, AdminButton, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  room: Room | null;
  cover: MediaItem[];
  gallery: MediaItem[];
}>();

const { t } = useI18n();
const isEdit = !!props.room;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.room as any)?.translations_data?.ar ?? {};

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
  translations: {
    ar: {
      name: rawTranslations.name ?? '',
      short_description: rawTranslations.short_description ?? '',
      description: rawTranslations.description ?? '',
      bed_type: rawTranslations.bed_type ?? '',
      view: rawTranslations.view ?? '',
    },
  },
});

const submit = () => {
  if (isEdit) form.put(`/admin/rooms/${props.room!.id}`);
  else form.post('/admin/rooms');
};

const statuses = [
  { value: 'draft', label: 'Draft (hidden)' },
  { value: 'published', label: 'Published' },
  { value: 'archived', label: 'Archived' },
];
</script>

<template>
  <div class="max-w-3xl space-y-6">
    <PageHeader
      :title="isEdit ? t('admin.edit_room', undefined, 'Edit Room & Suite') : t('admin.add_room', undefined, 'Add Room & Suite')"
      :subtitle="isEdit ? t('admin.room_edit_desc', undefined, 'Update room details, pricing specifications, and features.') : t('admin.room_create_desc', undefined, 'Add a new accommodation type to your hotel.')"
      back-url="/admin/rooms"
      :back-label="t('admin.all_rooms', undefined, 'All rooms')"
    />

    <form @submit.prevent="submit" class="space-y-4">
      <AdminCard>
        <div class="mb-4">
          <BilingualTabs v-model="activeTab" />
        </div>

        <!-- Arabic Translation Fields -->
        <div v-if="activeTab === 'ar'" class="space-y-4">
          <AdminInput
            v-model="form.translations.ar.name"
            :label="t('admin.common.name_ar', undefined, 'Name (AR) / الاسم بالعربية')"
            dir="rtl"
            :placeholder="form.name || 'اسم الغرفة أو الجناح بالعربية...'"
            :hint="`EN: ${form.name || '—'}`"
          />

          <AdminInput
            v-model="form.translations.ar.short_description"
            :label="t('admin.common.short_description_ar', undefined, 'Short Description (AR) / الوصف القصير بالعربية')"
            dir="rtl"
            :placeholder="form.short_description || 'نبذة قصيرة بالعربية...'"
          />

          <AdminTextarea
            v-model="form.translations.ar.description"
            :label="t('admin.common.description_ar', undefined, 'Description (AR) / الوصف التفصيلي بالعربية')"
            :rows="4"
            dir="rtl"
            :placeholder="form.description || 'الوصف الكامل بالعربية...'"
          />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <AdminInput
              v-model="form.translations.ar.bed_type"
              :label="`${t('rooms.bed', undefined, 'Bed')} (AR)`"
              dir="rtl"
              :placeholder="form.bed_type || 'سرير كينغ مريح'"
            />
            <AdminInput
              v-model="form.translations.ar.view"
              :label="`${t('rooms.view', undefined, 'View')} (AR)`"
              dir="rtl"
              :placeholder="form.view || 'إطلالة بانورامية على البحر'"
            />
          </div>
        </div>

        <!-- English & Base Fields -->
        <div v-else class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <AdminInput
              v-model="form.name"
              :label="t('admin.common.name_en', undefined, 'Name (EN)')"
              :error="form.errors.name"
              placeholder="e.g. Deluxe Sea View Room"
              required
            />
            <AdminInput
              v-model="form.slug"
              :label="t('admin.common.slug', undefined, 'Slug')"
              :error="form.errors.slug"
              placeholder="auto from name"
              required
            />
          </div>

          <AdminInput
            v-model="form.short_description"
            :label="t('admin.common.short_description_en', undefined, 'Short description')"
          />

          <AdminTextarea
            v-model="form.description"
            :label="t('admin.common.description_en', undefined, 'Description')"
            :rows="4"
          />

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <AdminInput
              v-model.number="form.size_sqm"
              type="number"
              :label="`${t('rooms.size', undefined, 'Size')} (m²)`"
              min="1"
            />
            <AdminInput
              v-model.number="form.max_guests"
              type="number"
              :label="t('rooms.occupancy', undefined, 'Max guests')"
              min="1"
            />
            <AdminInput
              v-model="form.bed_type"
              :label="t('rooms.bed', undefined, 'Bed')"
              placeholder="King"
            />
            <AdminInput
              v-model="form.view"
              :label="t('rooms.view', undefined, 'View')"
              placeholder="Sea"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.amenities', undefined, 'Features') }}
            </label>
            <TagListInput
              v-model="form.features"
              :placeholder="t('admin.common.features_placeholder', undefined, 'e.g. Balcony — press Enter to add')"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <AdminSelect
              v-model="form.status"
              :label="t('admin.common.status', undefined, 'Status')"
              :options="statuses"
            />
            <AdminInput
              v-model.number="form.sort_order"
              type="number"
              min="0"
              :label="t('admin.common.sort_order', undefined, 'Order')"
            />
            <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700 cursor-pointer">
              <input v-model="form.featured" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
              <span class="font-medium">{{ t('admin.common.featured', undefined, 'Featured Room') }}</span>
            </label>
          </div>
        </div>

        <template #footer>
          <div class="flex justify-end pt-1">
            <AdminButton
              type="submit"
              :loading="form.processing"
              :disabled="form.processing"
            >
              {{ isEdit ? t('admin.common.save', undefined, 'Save changes') : t('common.create', undefined, 'Create room') }}
            </AdminButton>
          </div>
        </template>
      </AdminCard>
    </form>

    <AdminCard
      :title="t('admin.rooms.photos_title', undefined, 'Photos & Gallery')"
      :subtitle="t('admin.rooms.photos_desc', undefined, 'Upload high-resolution photography for this room.')"
    >
      <template v-if="isEdit">
        <div class="space-y-6">
          <MediaManager mediable-type="room" :mediable-id="room!.id" collection="cover" :items="cover" label="Main photo" hint="Shown on the room card and at the top of its page." />
          <MediaManager mediable-type="room" :mediable-id="room!.id" collection="gallery" :items="gallery" label="Gallery" hint="Guests can tap a photo to view it full screen." />
        </div>
      </template>
      <p v-else class="text-sm text-slate-500">
        {{ t('admin.rooms.save_first_photos', undefined, 'Save the room first, then you can add photos.') }}
      </p>
    </AdminCard>
  </div>
</template>
