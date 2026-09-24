<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useI18n } from '@/i18n';

withDefaults(
    defineProps<{
        hotel?: Hotel;
        clock?: string;
        showClock?: boolean;
        onHome?: boolean;
    }>(),
    {
        clock: undefined,
        showClock: true,
        onHome: false,
    }
);

const { t } = useI18n();
</script>

<template>
    <header
        class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-6 lg:px-10 text-white backdrop-blur-md border-b border-white/10 transition-colors duration-200"
        style="height: var(--kiosk-topbar-h, 5rem); background: var(--header-bg, rgba(10, 12, 16, 0.82))"
    >
        <!-- Hotel Identity / Brand -->
        <Link href="/" class="flex items-center gap-2.5 text-2xl tracking-tight select-none group" style="font-family: var(--font-display)">
            <span
                class="h-2.5 w-2.5 rounded-full shrink-0 transition-transform group-hover:scale-125"
                style="background-color: var(--color-primary, #059669)"
            />
            <span class="truncate max-w-xs sm:max-w-md">{{ hotel?.name ?? 'Grand Horizon' }}</span>
        </Link>

        <!-- Right Tools: Language, Home, Clock -->
        <div class="flex items-center gap-3 sm:gap-4 lg:gap-6">
            <LanguageSwitcher variant="guest" />

            <Link
                v-if="!onHome"
                href="/"
                class="flex items-center gap-1.5 h-9 px-4 sm:px-5 rounded-full border border-white/25 bg-white/10 text-sm sm:text-base uppercase tracking-wider transition-all duration-150 hover:bg-white/20 active:scale-95 hover:border-[var(--color-primary,#059669)]"
            >
                <span class="inline-block transition-transform rtl:rotate-180">←</span>
                <span>{{ t('common.home', undefined, 'Home') }}</span>
            </Link>

            <span
                v-if="showClock && clock"
                class="text-lg sm:text-xl tabular-nums text-white/80 font-mono hidden sm:inline-block"
                aria-label="Current time"
            >
                {{ clock }}
            </span>
        </div>
    </header>
</template>
