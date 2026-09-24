<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';

withDefaults(
    defineProps<{
        hotelName?: string;
    }>(),
    {
        hotelName: 'Grand Horizon',
    }
);

const { locale } = useI18n();
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="mt-auto border-t border-slate-200 bg-white px-6 py-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full shrink-0" style="background-color: var(--admin-primary, #059669)" />
                <span class="font-medium text-slate-700">{{ hotelName }}</span>
                <span>•</span>
                <span>© {{ currentYear }} {{ locale === 'ar' ? 'نظام إدارة المحتوى الفندقي' : 'Hospitality CMS' }}</span>
            </div>

            <div class="flex items-center gap-4">
                <a
                    href="/"
                    target="_blank"
                    class="font-medium text-slate-600 hover:text-slate-900 transition-colors inline-flex items-center gap-1"
                >
                    <span>{{ locale === 'ar' ? 'عرض موقع النزيل' : 'View Guest Site' }}</span>
                    <span class="text-[10px]">↗</span>
                </a>
                <Link
                    href="/admin/theme"
                    class="text-slate-500 hover:text-slate-800 transition-colors"
                >
                    {{ locale === 'ar' ? 'المظهر والألوان' : 'Theme' }}
                </Link>
                <Link
                    href="/admin/settings"
                    class="text-slate-500 hover:text-slate-800 transition-colors"
                >
                    {{ locale === 'ar' ? 'الإعدادات' : 'Settings' }}
                </Link>
            </div>
        </div>
    </footer>
</template>
