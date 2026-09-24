<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { AdminCard, AdminButton, AdminThemeSelector } from '@/Components/Admin';
import { useI18n } from '@/i18n';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    guestView: 'classic' | 'tv';
}>();

const { t, locale } = useI18n();

const form = useForm({
    guest_view: props.guestView,
});

function save() {
    form.patch('/admin/settings/guest-view');
}
</script>

<template>
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800 mb-1">{{ t('admin.settings.title', undefined, 'Settings') }}</h1>
            <p class="text-sm text-slate-500">{{ t('admin.settings.subtitle', undefined, 'Manage your dashboard appearance and guest site layouts.') }}</p>
        </div>

        <!-- Admin Color Theme -->
        <AdminCard
            :title="locale === 'ar' ? 'مظهر لوحة التحكم (اللون الرئيسي)' : 'Admin Dashboard Color Theme'"
            :subtitle="locale === 'ar' ? 'اختر النمط اللوني المفضل للوحة التحكم (يدعم الأخضر والألوان الأخرى)' : 'Select your preferred accent color for buttons, navigation, and badges.'"
        >
            <AdminThemeSelector variant="expanded" />
        </AdminCard>

        <!-- Guest View Layout -->
        <AdminCard
            :title="t('admin.settings.guest_view', undefined, 'Guest View Layout')"
            :subtitle="t('admin.settings.guest_view_desc', undefined, 'Choose the overall layout guests see across the whole site.')"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label
                    class="cursor-pointer rounded-lg border p-4 transition-all"
                    :class="form.guest_view === 'classic' ? 'border-slate-800 bg-slate-50/80 shadow-xs ring-2 ring-slate-800/10' : 'border-slate-200 hover:border-slate-300'"
                >
                    <input v-model="form.guest_view" type="radio" value="classic" class="sr-only" />
                    <span class="block font-medium text-slate-800 mb-1">{{ t('admin.settings.classic_title', undefined, 'Classic Hospitality Shell') }}</span>
                    <span class="block text-xs text-slate-500">
                        {{ t('admin.settings.classic_desc', undefined, 'The current hospitality-screen shell: a top bar with the hotel name/clock and a bottom dock of navigation buttons.') }}
                    </span>
                </label>

                <label
                    class="cursor-pointer rounded-lg border p-4 transition-all"
                    :class="form.guest_view === 'tv' ? 'border-slate-800 bg-slate-50/80 shadow-xs ring-2 ring-slate-800/10' : 'border-slate-200 hover:border-slate-300'"
                >
                    <input v-model="form.guest_view" type="radio" value="tv" class="sr-only" />
                    <span class="block font-medium text-slate-800 mb-1">{{ t('admin.settings.tv_title', undefined, 'Smart TV Shell') }}</span>
                    <span class="block text-xs text-slate-500">
                        {{ t('admin.settings.tv_desc', undefined, 'Samsung-Smart-TV-style shell: no persistent top bar or dock - pages drive their own tile-based navigation.') }}
                    </span>
                </label>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <AdminButton
                    type="button"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="save"
                >
                    {{ form.processing ? t('admin.common.saving', undefined, 'Saving…') : t('admin.common.save', undefined, 'Save Layout Preference') }}
                </AdminButton>
                <span v-if="form.recentlySuccessful" class="text-sm font-medium text-emerald-600">
                    {{ t('admin.common.saved', undefined, 'Saved successfully.') }}
                </span>
            </div>
        </AdminCard>

        <!-- Guest Theme & Colors Card -->
        <AdminCard
            :title="locale === 'ar' ? 'ألوان وهوية موقع النزيل (المظهر العام)' : 'Guest Layout Theme & Branding'"
            :subtitle="locale === 'ar' ? 'تخصيص ألوان الواجهة التي يراها النزيل (النمط الأخضر الفاخر، الأزرار، والخطوط)' : 'Customize the public guest site palette, green style presets, and typography.'"
        >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg bg-emerald-50/50 border border-emerald-100">
                <div class="flex items-center gap-3">
                    <span class="h-10 w-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                        🎨
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">
                            {{ locale === 'ar' ? 'تخصيص ألوان تجربة النزيل' : 'Guest Layout Colors & Presets' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ locale === 'ar' ? 'اختر النمط الأخضر الفاخر أو خصص ألوان الأزرار والشرائط' : 'Configure emerald green, luxury forest, or custom brand colors with live preview.' }}
                        </p>
                    </div>
                </div>
                <AdminButton href="/admin/theme" variant="secondary" size="sm">
                    {{ locale === 'ar' ? 'فتح إعدادات المظهر ←' : 'Open Theme Editor →' }}
                </AdminButton>
            </div>
        </AdminCard>
    </div>
</template>
