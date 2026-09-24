<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import type { Facility } from '@/types/facility';
import type { MediaItem } from '@/types/room';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import TagListInput from '@/Components/Admin/TagListInput.vue';
import { AdminCard, AdminInput, AdminTextarea, AdminSelect, AdminButton, PageHeader } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  facility: Facility | null;
  cover?: MediaItem[];
  gallery?: MediaItem[];
  default_category?: string | null;
}>();

const { t } = useI18n();
const isEdit = !!props.facility;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.facility as any)?.translations_data?.ar ?? {};

const form = useForm({
  name: props.facility?.name ?? '',
  slug: props.facility?.slug ?? '',
  description: props.facility?.description ?? '',
  short_description: props.facility?.short_description ?? '',
  category: props.facility?.category ?? props.default_category ?? 'other',
  building: props.facility?.building ?? '',
  floor: props.facility?.floor ?? '',
  wing: props.facility?.wing ?? '',
  phone: props.facility?.phone ?? '',
  email: props.facility?.email ?? '',
  amenities: [...(props.facility?.amenities ?? [])],
  status: props.facility?.status ?? 'draft',
  translations: {
    ar: {
      name: rawTranslations.name ?? '',
      short_description: rawTranslations.short_description ?? '',
      description: rawTranslations.description ?? '',
      building: rawTranslations.building ?? '',
      floor: rawTranslations.floor ?? '',
      wing: rawTranslations.wing ?? '',
    },
  },
});

const submit = () => {
  if (isEdit) {
    form.put(`/admin/facilities/${props.facility!.id}`);
  } else {
    form.post('/admin/facilities');
  }
};

const categories = [
  { value: 'wellness', label: 'Wellness' },
  { value: 'fitness', label: 'Fitness' },
  { value: 'pool', label: 'Pool' },
  { value: 'kids', label: 'Kids' },
  { value: 'business', label: 'Business' },
  { value: 'beach', label: 'Beach' },
  { value: 'meeting', label: 'Meeting' },
  { value: 'other', label: 'Other' },
];

const statuses = [
  { value: 'draft', label: 'Draft' },
  { value: 'published', label: 'Published' },
  { value: 'archived', label: 'Archived' },
];
</script>

<template>
  <div class="max-w-2xl space-y-6">
    <PageHeader
      :title="isEdit ? t('admin.edit_facility', undefined, 'Edit Facility') : t('admin.add_facility', undefined, 'Add Facility')"
      :subtitle="isEdit ? t('admin.facility_edit_desc', undefined, 'Update details and amenities for this facility.') : t('admin.facility_create_desc', undefined, 'Add a new facility or meeting room.')"
      back-url="/admin/facilities"
      :back-label="t('admin.all_facilities', undefined, 'All facilities')"
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
            :placeholder="form.name || 'الاسم بالعربية...'"
            :hint="`EN: ${form.name || '—'}`"
          />

          <AdminInput
            v-model="form.translations.ar.short_description"
            :label="t('admin.common.short_description_ar', undefined, 'Short Description (AR) / الوصف القصير بالعربية')"
            dir="rtl"
            :placeholder="form.short_description || 'نبذة قصيرة بالعربية...'"
            :hint="`EN: ${form.short_description || '—'}`"
          />

          <AdminTextarea
            v-model="form.translations.ar.description"
            :label="t('admin.common.description_ar', undefined, 'Description (AR) / الوصف التفصيلي بالعربية')"
            :rows="4"
            dir="rtl"
            :placeholder="form.description || 'الوصف الكامل بالعربية...'"
          />

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <AdminInput
              v-model="form.translations.ar.building"
              :label="`${t('admin.common.building', undefined, 'Building')} (AR)`"
              dir="rtl"
              :placeholder="form.building || 'المبنى...'"
            />
            <AdminInput
              v-model="form.translations.ar.floor"
              :label="`${t('admin.common.floor', undefined, 'Floor')} (AR)`"
              dir="rtl"
              :placeholder="form.floor || 'الطابق...'"
            />
            <AdminInput
              v-model="form.translations.ar.wing"
              :label="`${t('admin.common.wing', undefined, 'Wing')} (AR)`"
              dir="rtl"
              :placeholder="form.wing || 'الجناح...'"
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
              required
            />
            <AdminInput
              v-model="form.slug"
              :label="t('admin.common.slug', undefined, 'Slug')"
              :error="form.errors.slug"
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

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <AdminSelect
              v-model="form.category"
              :label="t('admin.common.category', undefined, 'Category')"
              :options="categories"
            />
            <AdminInput
              v-model="form.building"
              :label="t('admin.common.building', undefined, 'Building')"
            />
            <AdminInput
              v-model="form.floor"
              :label="t('admin.common.floor', undefined, 'Floor')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.features', undefined, 'Features') }}
            </label>
            <TagListInput
              v-model="form.amenities"
              :placeholder="t('admin.common.features_placeholder', undefined, 'e.g. Projector, 20 seats — press Enter to add')"
            />
          </div>

          <AdminSelect
            v-model="form.status"
            :label="t('admin.common.status', undefined, 'Status')"
            :options="statuses"
          />
        </div>

        <template #footer>
          <div class="flex justify-end gap-2">
            <AdminButton
              type="submit"
              :loading="form.processing"
              :disabled="form.processing"
            >
              {{ isEdit ? t('admin.common.save', undefined, 'Save changes') : t('common.create', undefined, 'Create facility') }}
            </AdminButton>
          </div>
        </template>
      </AdminCard>
    </form>

    <AdminCard
      :title="t('admin.facilities.media_title', undefined, 'Media & Photos')"
      :subtitle="t('admin.facilities.media_desc', undefined, 'Manage cover photo and gallery images.')"
    >
      <template v-if="isEdit">
        <div class="space-y-6">
          <MediaManager mediable-type="facility" :mediable-id="facility!.id" collection="cover" :items="cover ?? []" label="Main photo" hint="Shown on the card and at the top of the page." />
          <MediaManager mediable-type="facility" :mediable-id="facility!.id" collection="gallery" :items="gallery ?? []" label="Gallery" hint="Guests can tap a photo to view it full screen." />
        </div>
      </template>
      <p v-else class="text-sm text-slate-500">
        {{ t('admin.common.save_first_photos', undefined, 'Save first, then you can add photos.') }}
      </p>
    </AdminCard>
  </div>
</template>
