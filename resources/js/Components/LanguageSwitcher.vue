<script setup lang="ts">
import { useI18n, type Locale } from '@/i18n';

withDefaults(
    defineProps<{
        variant?: 'guest' | 'admin' | 'minimal';
    }>(),
    {
        variant: 'guest',
    },
);

const { locale, switchLocale } = useI18n();

function change(target: Locale) {
    if (target !== locale.value) {
        switchLocale(target);
    }
}
</script>

<template>
    <!-- Guest Kiosk Variant -->
    <div
        v-if="variant === 'guest'"
        class="inline-flex items-center rounded-full border border-white/25 bg-black/30 p-1 text-sm backdrop-blur-md transition-all"
        data-testid="language-switcher-guest"
        role="group"
        aria-label="Language selection"
    >
        <button
            type="button"
            class="flex h-8 items-center px-3.5 rounded-full font-medium transition-all duration-150 active:scale-95"
            :class="
                locale === 'en'
                    ? 'bg-white text-slate-900 shadow-sm'
                    : 'text-white/80 hover:text-white'
            "
            :aria-pressed="locale === 'en'"
            @click="change('en')"
        >
            EN
        </button>
        <button
            type="button"
            class="flex h-8 items-center px-3.5 rounded-full font-medium transition-all duration-150 active:scale-95 font-arabic"
            :class="
                locale === 'ar'
                    ? 'text-slate-900 shadow-sm'
                    : 'text-white/80 hover:text-white'
            "
            :style="locale === 'ar' ? { background: 'var(--luxury-champagne, #b99a62)' } : undefined"
            :aria-pressed="locale === 'ar'"
            @click="change('ar')"
        >
            العربية
        </button>
    </div>

    <!-- Admin Panel Variant -->
    <div
        v-else-if="variant === 'admin'"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-xs font-medium text-slate-600"
        data-testid="language-switcher-admin"
        role="group"
        aria-label="Language selection"
    >
        <button
            type="button"
            class="flex h-7 items-center rounded-md px-2.5 transition-colors"
            :class="
                locale === 'en'
                    ? 'bg-white font-semibold text-slate-900 shadow-xs'
                    : 'hover:text-slate-900'
            "
            :aria-pressed="locale === 'en'"
            @click="change('en')"
        >
            English
        </button>
        <button
            type="button"
            class="flex h-7 items-center rounded-md px-2.5 transition-colors font-arabic"
            :class="
                locale === 'ar'
                    ? 'bg-white font-semibold text-slate-900 shadow-xs'
                    : 'hover:text-slate-900'
            "
            :aria-pressed="locale === 'ar'"
            @click="change('ar')"
        >
            العربية
        </button>
    </div>

    <!-- Minimal Variant -->
    <div
        v-else
        class="inline-flex items-center gap-2 text-sm"
        data-testid="language-switcher-minimal"
    >
        <button
            type="button"
            class="transition-opacity hover:opacity-100"
            :class="locale === 'en' ? 'font-bold opacity-100 underline underline-offset-4' : 'opacity-60'"
            @click="change('en')"
        >
            EN
        </button>
        <span class="opacity-40">|</span>
        <button
            type="button"
            class="transition-opacity hover:opacity-100 font-arabic"
            :class="locale === 'ar' ? 'font-bold opacity-100 underline underline-offset-4' : 'opacity-60'"
            @click="change('ar')"
        >
            العربية
        </button>
    </div>
</template>
