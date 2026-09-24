<script setup lang="ts">
import { useI18n } from '@/i18n';

const modelValue = defineModel<'en' | 'ar'>({ default: 'en' });
const { t } = useI18n();
</script>

<template>
  <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-5">
    <div class="flex items-center gap-2">
      <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
        {{ t('admin.common.language', undefined, 'Language') }}:
      </span>
      <div class="inline-flex rounded-lg bg-slate-100 p-1 text-xs font-medium">
        <button
          type="button"
          class="flex items-center gap-1.5 px-3 py-1 rounded-md transition-all"
          :class="modelValue === 'en' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-600 hover:text-slate-900'"
          @click="modelValue = 'en'"
        >
          <span>🇬🇧</span>
          <span>English</span>
        </button>
        <button
          type="button"
          class="flex items-center gap-1.5 px-3 py-1 rounded-md transition-all"
          :class="modelValue === 'ar' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-600 hover:text-slate-900'"
          @click="modelValue = 'ar'"
        >
          <span>🇸🇦</span>
          <span>العربية (Arabic)</span>
        </button>
      </div>
    </div>

    <div v-if="modelValue === 'ar'" class="text-xs text-amber-700 bg-amber-50 px-2.5 py-1 rounded border border-amber-200">
      {{ t('admin.common.arabic_fallback_hint', undefined, 'Untranslated Arabic fields will automatically fall back to English.') }}
    </div>
  </div>
</template>
