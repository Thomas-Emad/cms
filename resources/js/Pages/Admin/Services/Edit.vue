<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import type { Service } from '@/types/content';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ service: Service | null }>();
const { t } = useI18n();
const isEdit = !!props.service;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.service as any)?.translations_data?.ar ?? {};

const form = useForm({
  name: props.service?.name ?? '',
  slug: props.service?.slug ?? '',
  description: props.service?.description ?? '',
  icon: props.service?.icon ?? '',
  contact: props.service?.contact ?? '',
  price: props.service?.price ?? '',
  request_enabled: props.service?.request_enabled ?? false,
  status: props.service?.status ?? 'draft',
  translations: {
    ar: {
      name: rawTranslations.name ?? '',
      description: rawTranslations.description ?? '',
    },
  },
});

const submit = () => {
  if (isEdit) form.put(`/admin/services/${props.service!.id}`);
  else form.post('/admin/services');
};
</script>

<template>
  <div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ isEdit ? t('admin.services.edit', 'Edit Service') : t('admin.services.create', 'Add Service') }}</h1>
      <BilingualTabs v-model="activeTab" />
    </div>

    <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
      <!-- English Fields -->
      <div v-show="activeTab === 'en'" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.name', 'Name') }} (EN)</label>
            <input v-model="form.name" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.slug', 'Slug') }}</label>
            <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.description', 'Description') }} (EN)</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Arabic Fields -->
      <div v-show="activeTab === 'ar'" class="space-y-4" dir="rtl">
        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2 mb-2">
          {{ t('admin.bilingual.arabic_notice', 'Arabic translations for guest view. Leave empty to fallback to English.') }}
        </p>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.name', 'Name') }} (العربية)</label>
          <input v-model="form.translations.ar.name" type="text" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.description', 'Description') }} (العربية)</label>
          <textarea v-model="form.translations.ar.description" rows="3" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Shared / Operational Settings -->
      <div class="border-t border-slate-200 pt-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.price', 'Price (blank = complimentary/on request)') }}</label>
            <input v-model="form.price" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.services.form.contact', 'Contact') }}</label>
            <input v-model="form.contact" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.request_enabled" type="checkbox" class="rounded border-slate-300" />
          {{ t('admin.services.form.request_enabled', 'Guests can submit a request for this service from the app') }}
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
          {{ isEdit ? t('admin.common.save', 'Save changes') : t('admin.services.create_btn', 'Create service') }}
        </button>
      </div>
    </form>
  </div>
</template>
