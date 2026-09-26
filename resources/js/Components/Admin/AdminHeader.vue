<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { SharedPageProps } from '@/types/hotel';
import { useI18n } from '@/i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import AdminThemeSelector from './AdminThemeSelector.vue';

withDefaults(
  defineProps<{
    title?: string;
    showGuestPreview?: boolean;
  }>(),
  {
    title: undefined,
    showGuestPreview: true,
  }
);

defineEmits<{
  (e: 'toggleSidebar'): void;
}>();

const page = usePage<SharedPageProps>();
const { t, locale } = useI18n();
const isSuperAdmin = computed(() => page.props.auth?.user?.role === 'super_admin');
</script>

<template>
  <header class="border-b border-slate-200 bg-white px-4 sm:px-6 py-2.5 flex items-center justify-between gap-4 sticky top-0 z-30 shadow-xs">
    <!-- Left: Mobile Menu Toggle & Title -->
    <div class="flex items-center gap-3 min-w-0">
      <button
        type="button"
        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-500 hover:text-slate-800 hover:bg-slate-100 focus:outline-hidden focus:ring-2 focus:ring-slate-400"
        :aria-label="locale === 'ar' ? 'القائمة الجانبية' : 'Toggle navigation'"
        @click="$emit('toggleSidebar')"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <div class="flex items-center gap-2">
        <span class="h-2 w-2 rounded-full hidden sm:inline-block" style="background-color: var(--admin-primary, #059669)" />
        <h2 class="text-sm font-semibold text-slate-700 truncate">
          {{ title ?? (isSuperAdmin ? (locale === 'ar' ? 'لوحة تحكم المنصة الرئيسية' : 'Platform Administration') : t('nav.admin_dashboard', undefined, 'Admin Dashboard')) }}
        </h2>
      </div>
    </div>

    <!-- Right: Quick actions, Theme, Lang, User & Logout -->
    <div class="flex items-center gap-2 sm:gap-3.5">
      <!-- Quick Guest Preview Button (hidden for platform super admin managing all accounts) -->
      <a
        v-if="!isSuperAdmin && showGuestPreview && page.props.hotel"
        href="/"
        target="_blank"
        rel="noopener noreferrer"
        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 transition-colors"
        :title="locale === 'ar' ? 'معاينة موقع النزيل في نافذة جديدة' : 'Open live guest view in new tab'"
      >
        <span>👁️</span>
        <span>{{ locale === 'ar' ? 'عرض موقع النزيل' : 'Guest View' }}</span>
        <span class="text-slate-400 text-[10px]">↗</span>
      </a>

      <!-- Color Theme Switcher -->
      <AdminThemeSelector variant="compact" />

      <!-- Language Selector -->
      <LanguageSwitcher variant="admin" />

      <div class="h-4 w-px bg-slate-200 hidden sm:block" />

      <!-- User Profile -->
      <div class="flex items-center gap-2">
        <span
          class="h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-xs"
          style="background-color: var(--admin-primary, #059669)"
        >
          {{ (page.props.auth?.user?.name ?? 'A').charAt(0).toUpperCase() }}
        </span>
        <div class="hidden lg:flex flex-col">
          <span class="text-xs font-medium text-slate-700 max-w-[120px] truncate leading-tight">
            {{ page.props.auth?.user?.name }}
          </span>
          <span v-if="isSuperAdmin" class="text-[10px] font-semibold text-indigo-600 leading-tight">
            Super Admin
          </span>
        </div>
      </div>

      <!-- Logout -->
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors"
        :title="t('nav.logout', undefined, 'Log out')"
      >
        <span class="hidden sm:inline">{{ t('nav.logout', undefined, 'Log out') }}</span>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </Link>
    </div>
  </header>
</template>
