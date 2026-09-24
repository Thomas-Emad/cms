<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import type { Facility } from '@/types/facility';
import type { MediaItem } from '@/types/room';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import TagListInput from '@/Components/Admin/TagListInput.vue';

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

const categories = ['wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other'];
</script>

<template>
  <div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">
        {{ isEdit ? t('admin.edit_facility', undefined, 'Edit Facility') : t('admin.add_facility', undefined, 'Add Facility') }}
      </h1>
      <Link href="/admin/facilities" class="text-sm text-slate-500 hover:text-slate-800">
        {{ t('admin.all_facilities', undefined, '← All facilities') }}
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
            :placeholder="form.name || 'الاسم بالعربية...'"
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
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
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
          />
          <p class="mt-1 text-xs text-slate-400">EN: {{ form.short_description || '—' }}</p>
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
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
          />
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.building', undefined, 'Building') }} (AR)
            </label>
            <input
              v-model="form.translations.ar.building"
              type="text"
              dir="rtl"
              :placeholder="form.building || 'المبنى...'"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.floor', undefined, 'Floor') }} (AR)
            </label>
            <input
              v-model="form.translations.ar.floor"
              type="text"
              dir="rtl"
              :placeholder="form.floor || 'الطابق...'"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.wing', undefined, 'Wing') }} (AR)
            </label>
            <input
              v-model="form.translations.ar.wing"
              type="text"
              dir="rtl"
              :placeholder="form.wing || 'الجناح...'"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
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
            <input v-model="form.name" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.slug', undefined, 'Slug') }}
            </label>
            <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.short_description_en', undefined, 'Short description') }}
          </label>
          <input v-model="form.short_description" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.description_en', undefined, 'Description') }}
          </label>
          <textarea v-model="form.description" rows="4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.category', undefined, 'Category') }}
            </label>
            <select v-model="form.category" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
              <option v-for="c in categories" :key="c" :value="c">{{ t('facilities.categories.' + c, undefined, c) }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.building', undefined, 'Building') }}
            </label>
            <input v-model="form.building" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.floor', undefined, 'Floor') }}
            </label>
            <input v-model="form.floor" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.features', undefined, 'Features') }}
          </label>
          <TagListInput v-model="form.amenities" :placeholder="t('admin.common.features_placeholder', undefined, 'e.g. Projector, 20 seats — press Enter to add')" />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            {{ t('admin.common.status', undefined, 'Status') }}
          </label>
          <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="draft">{{ t('status.draft', undefined, 'Draft') }}</option>
            <option value="published">{{ t('status.published', undefined, 'Published') }}</option>
            <option value="archived">{{ t('status.archived', undefined, 'Archived') }}</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50"
        >
          {{ isEdit ? t('admin.common.save', undefined, 'Save changes') : t('common.create', undefined, 'Create facility') }}
        </button>
      </div>
    </form>

    <div class="mt-6 space-y-6 rounded-lg border border-slate-200 bg-white p-6">
      <template v-if="isEdit">
        <MediaManager mediable-type="facility" :mediable-id="facility!.id" collection="cover" :items="cover ?? []" label="Main photo" hint="Shown on the card and at the top of the page." />
        <MediaManager mediable-type="facility" :mediable-id="facility!.id" collection="gallery" :items="gallery ?? []" label="Gallery" hint="Guests can tap a photo to view it full screen." />
      </template>
      <p v-else class="text-sm text-slate-500">Save first, then you can add photos.</p>
    </div>
  </div>
</template>
