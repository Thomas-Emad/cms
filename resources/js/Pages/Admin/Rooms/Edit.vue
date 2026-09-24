<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import TagListInput from '@/Components/Admin/TagListInput.vue';
import type { MediaItem, Room } from '@/types/room';

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

const input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm';
</script>

<template>
  <div class="max-w-3xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">
        {{ isEdit ? t('admin.edit_room', undefined, 'Edit Room & Suite') : t('admin.add_room', undefined, 'Add Room & Suite') }}
      </h1>
      <Link href="/admin/rooms" class="text-sm text-slate-500 hover:text-slate-800">
        {{ t('admin.all_rooms', undefined, '← All rooms') }}
      </Link>
    </div>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <BilingualTabs v-model="activeTab" />

      <!-- Arabic Translation Fields -->
      <div v-if="activeTab === 'ar'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.name_ar', undefined, 'Name (AR) / الاسم بالعربية') }}
          </label>
          <input
            v-model="form.translations.ar.name"
            type="text"
            dir="rtl"
            :placeholder="form.name || 'اسم الغرفة أو الجناح بالعربية...'"
            :class="input"
          />
          <p class="mt-1 text-xs text-slate-400">EN: {{ form.name || '—' }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.short_description_ar', undefined, 'Short Description (AR) / الوصف القصير بالعربية') }}
          </label>
          <input
            v-model="form.translations.ar.short_description"
            type="text"
            dir="rtl"
            :placeholder="form.short_description || 'نبذة قصيرة بالعربية...'"
            :class="input"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.description_ar', undefined, 'Description (AR) / الوصف التفصيلي بالعربية') }}
          </label>
          <textarea
            v-model="form.translations.ar.description"
            rows="4"
            dir="rtl"
            :placeholder="form.description || 'الوصف الكامل بالعربية...'"
            :class="input"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.bed', undefined, 'Bed') }} (AR)
            </label>
            <input
              v-model="form.translations.ar.bed_type"
              type="text"
              dir="rtl"
              :placeholder="form.bed_type || 'سرير كينغ مريح'"
              :class="input"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.view', undefined, 'View') }} (AR)
            </label>
            <input
              v-model="form.translations.ar.view"
              type="text"
              dir="rtl"
              :placeholder="form.view || 'إطلالة بانورامية على البحر'"
              :class="input"
            />
          </div>
        </div>
      </div>

      <!-- English & Base Fields -->
      <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.name_en', undefined, 'Name (EN)') }}
            </label>
            <input v-model="form.name" type="text" :class="input" placeholder="e.g. Deluxe Sea View Room" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.slug', undefined, 'Slug') }}
            </label>
            <input v-model="form.slug" type="text" :class="input" placeholder="auto from name" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.short_description_en', undefined, 'Short description') }}
          </label>
          <input v-model="form.short_description" type="text" :class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.description_en', undefined, 'Description') }}
          </label>
          <textarea v-model="form.description" rows="4" :class="input" />
        </div>

        <div class="grid grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.size', undefined, 'Size') }} (m²)
            </label>
            <input v-model.number="form.size_sqm" type="number" min="1" :class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.occupancy', undefined, 'Max guests') }}
            </label>
            <input v-model.number="form.max_guests" type="number" min="1" :class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.bed', undefined, 'Bed') }}
            </label>
            <input v-model="form.bed_type" type="text" :class="input" placeholder="King" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('rooms.view', undefined, 'View') }}
            </label>
            <input v-model="form.view" type="text" :class="input" placeholder="Sea" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('rooms.amenities', undefined, 'Features') }}
          </label>
          <TagListInput v-model="form.features" :placeholder="t('admin.common.features_placeholder', undefined, 'e.g. Balcony — press Enter to add')" />
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.status', undefined, 'Status') }}
            </label>
            <select v-model="form.status" :class="input">
              <option value="draft">{{ t('status.draft', undefined, 'Draft (hidden)') }}</option>
              <option value="published">{{ t('status.published', undefined, 'Published') }}</option>
              <option value="archived">{{ t('status.archived', undefined, 'Archived') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.sort_order', undefined, 'Order') }}
            </label>
            <input v-model.number="form.sort_order" type="number" min="0" :class="input" />
          </div>
          <label class="flex items-end gap-2 pb-2 text-sm text-slate-700">
            <input v-model="form.featured" type="checkbox" /> {{ t('admin.common.featured', undefined, 'Featured') }}
          </label>
        </div>
      </div>

      <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? t('admin.common.save', undefined, 'Save changes') : t('common.create', undefined, 'Create room') }}
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
