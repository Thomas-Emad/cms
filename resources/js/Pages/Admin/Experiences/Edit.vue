<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { BranchSelector } from '@/Components/Admin';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import type { Experience } from '@/types/content';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ experience: Experience | null }>();
const { t } = useI18n();
const isEdit = !!props.experience;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.experience as any)?.translations_data?.ar ?? {};

const form = useForm({
  hotel_branch_id: (props.experience as any)?.hotel_branch_id ?? null,
  title: props.experience?.title ?? '',
  slug: props.experience?.slug ?? '',
  description: props.experience?.description ?? '',
  category: props.experience?.category ?? '',
  duration: props.experience?.duration ?? '',
  price: props.experience?.price ?? '',
  booking_url: props.experience?.booking_url ?? '',
  featured: props.experience?.featured ?? false,
  status: props.experience?.status ?? 'draft',
  translations: {
    ar: {
      title: rawTranslations.title ?? '',
      description: rawTranslations.description ?? '',
      duration: rawTranslations.duration ?? '',
    },
  },
});

const submit = () => {
  if (isEdit) form.put(`/admin/experiences/${props.experience!.id}`);
  else form.post('/admin/experiences');
};
</script>

<template>
  <div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ isEdit ? t('admin.experiences.edit', 'Edit Experience') : t('admin.experiences.create', 'Add Experience') }}</h1>
      <BilingualTabs v-model="activeTab" />
    </div>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <!-- English Fields -->
      <div v-show="activeTab === 'en'" class="space-y-4">
        <BranchSelector
          v-model="form.hotel_branch_id"
          :error="form.errors.hotel_branch_id"
        />

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.title', 'Title') }} (EN)</label>
            <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.slug', 'Slug') }}</label>
            <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.description', 'Description') }} (EN)</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.duration', 'Duration') }} (EN)</label>
          <input v-model="form.duration" type="text" placeholder="e.g. 2 hours" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Arabic Fields -->
      <div v-show="activeTab === 'ar'" class="space-y-4" dir="rtl">
        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2 mb-2">
          {{ t('admin.bilingual.arabic_notice', 'Arabic translations for guest view. Leave empty to fallback to English.') }}
        </p>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.title', 'Title') }} (العربية)</label>
          <input v-model="form.translations.ar.title" type="text" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.description', 'Description') }} (العربية)</label>
          <textarea v-model="form.translations.ar.description" rows="3" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.duration', 'Duration') }} (العربية)</label>
          <input v-model="form.translations.ar.duration" type="text" dir="rtl" placeholder="مثال: ساعتان" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Shared / Operational Settings -->
      <div class="border-t border-slate-200 pt-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.category', 'Category') }}</label>
            <input v-model="form.category" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.price', 'Price') }}</label>
            <input v-model="form.price" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.experiences.form.booking_url', 'Booking URL') }}</label>
          <input v-model="form.booking_url" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.featured" type="checkbox" class="rounded border-slate-300" />
          {{ t('admin.experiences.form.featured', 'Featured') }}
        </label>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.common.status', 'Status') }}</label>
          <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="draft">{{ t('admin.status.draft', 'Draft') }}</option>
            <option value="published">{{ t('admin.status.published', 'Published') }}</option>
            <option value="archived">{{ t('admin.status.archived', 'Archived') }}</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? t('admin.common.save', 'Save changes') : t('admin.experiences.create_btn', 'Create experience') }}
        </button>
      </div>
    </form>
  </div>
</template>
