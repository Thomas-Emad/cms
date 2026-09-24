<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';
import { type GuestLayoutConfig, localizedItemLabel, visibleItems } from './shellConfig';
import { useKioskShell } from './useKioskShell';
import { useI18n } from '@/i18n';
import GuestHeader from '@/Components/Guest/GuestHeader.vue';
// import GuestFooter from '@/Components/Guest/GuestFooter.vue';

/**
 * "Classic dock" template: slim dark top bar (hotel name + clock) and a large bottom dock of
 * pill buttons. Both are `fixed`, so every guest page keeps its own natural document scroll.
 * The top bar is ALWAYS dark so it is legible over photos and over white pages.
 */
const props = defineProps<{ hotel?: Hotel; config: GuestLayoutConfig; preview?: boolean }>();

const { onHome, isActive, clock } = useKioskShell({ skipFontScale: props.preview });
const { t } = useI18n();
</script>

<template>
    <div class="kiosk-root min-h-screen flex flex-col bg-white text-slate-900"
        style="font-family: var(--font-sans); --kiosk-topbar-h: 5rem; --kiosk-dock-h: 7.5rem" @contextmenu.prevent>
        <!-- Top bar: identity + time only. Handled by reusable GuestHeader component. -->
        <GuestHeader :hotel="hotel" :clock="clock" :show-clock="config.show_clock" :on-home="onHome" />

        <!-- No top padding: pages with a full-bleed hero start at y=0 behind the top bar. -->
        <main class="flex-1 flex flex-col" style="padding-bottom: var(--kiosk-dock-h)">
            <div class="flex-1">
                <slot />
            </div>

            <!-- Luxury Hospitality Guest Footer -->
            <!-- <GuestFooter :hotel="hotel" /> -->
        </main>

        <!-- Bottom dock: big touch targets, scrolls sideways if they don't all fit. -->
        <nav class="fixed bottom-0 inset-x-0 z-50 flex items-center backdrop-blur-md border-t border-white/10 transition-colors duration-200"
            style="height: var(--kiosk-dock-h); background: var(--dock-bg, var(--footer-bg, rgba(10, 12, 16, 0.88)))"
            aria-label="Main navigation">
            <div class="no-scrollbar w-full overflow-x-auto snap-x snap-proximity px-4">
                <ul class="flex gap-3 w-max mx-auto">
                    <li v-for="link in visibleItems(config)" :key="link.href" class="snap-center">
                        <Link :href="link.href" :aria-current="isActive(link.href) ? 'page' : undefined"
                            class="flex items-center justify-center h-[3rem] min-w-[5rem] px-4 rounded-full text-lg uppercase tracking-wide border transition-all duration-150 active:scale-95"
                            :class="isActive(link.href)
                                ? 'border-transparent shadow-md'
                                : 'border-white/20 text-white bg-white/10 hover:bg-white/20 hover:border-white/40'
                                "
                            :style="isActive(link.href) ? { background: 'var(--color-primary, var(--luxury-champagne))', color: '#ffffff' } : undefined">
                            {{ localizedItemLabel(link, t) }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</template>
