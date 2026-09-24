<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import type { Offer } from '@/types/content';
import { formatDate } from '@/lib/formatters';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ offer: Offer | null }>();
const { t } = useI18n();
const isEdit = !!props.offer;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.offer as any)?.translations_data?.ar ?? {};

const form = useForm({
  title: props.offer?.title ?? '',
  slug: props.offer?.slug ?? '',
  description: props.offer?.description ?? '',
  price: props.offer?.price ?? '',
  discount: props.offer?.discount ?? '',
  valid_from: formatDate(props.offer?.valid_from, true),
  valid_until: formatDate(props.offer?.valid_until, true),
  booking_url: props.offer?.booking_url ?? '',
  featured: props.offer?.featured ?? false,
  status: props.offer?.status ?? 'draft',
  translations: {
    ar: {
      title: rawTranslations.title ?? '',
      description: rawTranslations.description ?? '',
      discount: rawTranslations.discount ?? '',
    },
  },
});

const submit = () => {
  if (isEdit) form.put(`/admin/offers/${props.offer!.id}`);
  else form.post('/admin/offers');
};
</script>

<template>
  <div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ isEdit ? t('admin.offers.edit', 'Edit Offer') : t('admin.offers.create', 'Add Offer') }}</h1>
      <BilingualTabs v-model="activeTab" />
    </div>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <!-- English Fields -->
      <div v-show="activeTab === 'en'" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.title', 'Title') }} (EN)</label>
            <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.slug', 'Slug') }}</label>
            <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.description', 'Description') }} (EN)</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Arabic Fields -->
      <div v-show="activeTab === 'ar'" class="space-y-4" dir="rtl">
        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2 mb-2">
          {{ t('admin.bilingual.arabic_notice', 'Arabic translations for guest view. Leave empty to fallback to English.') }}
        </p>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.title', 'Title') }} (العربية)</label>
          <input v-model="form.translations.ar.title" type="text" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.description', 'Description') }} (العربية)</label>
          <textarea v-model="form.translations.ar.description" rows="3" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.discount_badge', 'Discount / Badge') }} (العربية)</label>
          <input v-model="form.translations.ar.discount" type="text" dir="rtl" placeholder="مثال: خصم 20%" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Shared / Operational Settings -->
      <div class="border-t border-slate-200 pt-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.price', 'Price') }}</label>
            <input v-model="form.price" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.discount', 'Discount %') }}</label>
            <input v-model="form.discount" type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.valid_from', 'Valid from') }}</label>
            <input v-model="form.valid_from" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.valid_until', 'Valid until') }}</label>
            <input v-model="form.valid_until" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.valid_until" class="mt-1 text-xs text-red-600">{{ form.errors.valid_until }}</p>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.offers.form.booking_url', 'Booking URL') }}</label>
          <input v-model="form.booking_url" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.featured" type="checkbox" class="rounded border-slate-300" />
          {{ t('admin.offers.form.featured_hint', 'Featured (eligible for homepage offer sections)') }}
        </label>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.common.status', 'Status') }}</label>
          <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="draft">{{ t('admin.status.draft', 'Draft') }}</option>
            <option value="published">{{ t('admin.status.published', 'Published') }}</option>
            <option value="expired">{{ t('admin.status.expired', 'Expired') }}</option>
            <option value="archived">{{ t('admin.status.archived', 'Archived') }}</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? t('admin.common.save', 'Save changes') : t('admin.offers.create_btn', 'Create offer') }}
        </button>
      </div>
    </form>
  </div>
</template>
