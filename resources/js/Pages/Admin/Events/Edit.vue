<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { BranchSelector } from '@/Components/Admin';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import type { HotelEvent } from '@/types/content';
import { formatDate } from '@/lib/formatters';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ event: HotelEvent | null }>();
const { t } = useI18n();
const isEdit = !!props.event;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.event as any)?.translations_data?.ar ?? {};

const form = useForm({
  hotel_branch_id: (props.event as any)?.hotel_branch_id ?? null,
  title: props.event?.title ?? '',
  slug: props.event?.slug ?? '',
  description: props.event?.description ?? '',
  start_date: formatDate(props.event?.start_date, true),
  end_date: formatDate(props.event?.end_date, true),
  start_time: props.event?.start_time ?? '',
  location: props.event?.location ?? '',
  capacity: props.event?.capacity ?? '',
  booking_required: props.event?.booking_required ?? false,
  booking_url: props.event?.booking_url ?? '',
  status: props.event?.status ?? 'draft',
  translations: {
    ar: {
      title: rawTranslations.title ?? '',
      description: rawTranslations.description ?? '',
      location: rawTranslations.location ?? '',
    },
  },
});

const submit = () => {
  if (isEdit) form.put(`/admin/events/${props.event!.id}`);
  else form.post('/admin/events');
};
</script>

<template>
  <div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">{{ isEdit ? t('admin.events.edit', 'Edit Event') : t('admin.events.create', 'Add Event') }}</h1>
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
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.title', 'Title') }} (EN)</label>
            <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.slug', 'Slug') }}</label>
            <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.description', 'Description') }} (EN)</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.location', 'Location') }} (EN)</label>
          <input v-model="form.location" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Arabic Fields -->
      <div v-show="activeTab === 'ar'" class="space-y-4" dir="rtl">
        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2 mb-2">
          {{ t('admin.bilingual.arabic_notice', 'Arabic translations for guest view. Leave empty to fallback to English.') }}
        </p>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.title', 'Title') }} (العربية)</label>
          <input v-model="form.translations.ar.title" type="text" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.description', 'Description') }} (العربية)</label>
          <textarea v-model="form.translations.ar.description" rows="3" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.location', 'Location') }} (العربية)</label>
          <input v-model="form.translations.ar.location" type="text" dir="rtl" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>
      </div>

      <!-- Shared / Operational Settings -->
      <div class="border-t border-slate-200 pt-4 space-y-4">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.start_date', 'Start date') }}</label>
            <input v-model="form.start_date" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.end_date', 'End date') }}</label>
            <input v-model="form.end_date" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.start_time', 'Start time') }}</label>
            <input v-model="form.start_time" type="time" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.capacity', 'Capacity') }}</label>
          <input v-model="form.capacity" type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
          <input v-model="form.booking_required" type="checkbox" class="rounded border-slate-300" />
          {{ t('admin.events.form.booking_required', 'Booking required') }}
        </label>
        <div v-if="form.booking_required">
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.events.form.booking_url', 'Booking URL') }}</label>
          <input v-model="form.booking_url" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.booking_url" class="mt-1 text-xs text-red-600">{{ form.errors.booking_url }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('admin.common.status', 'Status') }}</label>
          <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="draft">{{ t('admin.status.draft', 'Draft') }}</option>
            <option value="published">{{ t('admin.status.published', 'Published') }}</option>
            <option value="cancelled">{{ t('admin.status.cancelled', 'Cancelled') }}</option>
            <option value="archived">{{ t('admin.status.archived', 'Archived') }}</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
          {{ isEdit ? t('admin.common.save', 'Save changes') : t('admin.events.create_btn', 'Create event') }}
        </button>
      </div>
    </form>
  </div>
</template>
