<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';
import { type GuestLayoutConfig, visibleItems } from './shellConfig';
import { useKioskShell } from './useKioskShell';

/**
 * "Classic dock" template: slim dark top bar (hotel name + clock) and a large bottom dock of
 * pill buttons. Both are `fixed`, so every guest page keeps its own natural document scroll.
 * The top bar is ALWAYS dark so it is legible over photos and over white pages.
 */
const props = defineProps<{ hotel?: Hotel; config: GuestLayoutConfig; preview?: boolean }>();

const { onHome, isActive, clock } = useKioskShell({ skipFontScale: props.preview });
</script>

<template>
    <div class="kiosk-root min-h-screen flex flex-col bg-white text-slate-900"
        style="font-family: var(--font-sans); --kiosk-topbar-h: 5rem; --kiosk-dock-h: 7.5rem" @contextmenu.prevent>
        <!-- Top bar: identity + time only. Navigation lives in the dock. -->
        <header class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-10 text-white backdrop-blur-md"
            style="height: var(--kiosk-topbar-h); background: rgba(10, 12, 16, 0.82)">
            <Link href="/" class="text-2xl tracking-tight" style="font-family: var(--font-display)">
                {{ hotel?.name ?? 'Hotel' }}
            </Link>
            <div class="flex items-center gap-6">
                <Link v-if="!onHome" href="/"
                    class="flex items-center h-9 px-6 rounded-full border border-white/25 bg-white/10 text-base uppercase tracking-wider transition-transform duration-150 active:scale-95">
                    ← Home
                </Link>
                <span v-if="config.show_clock" class="text-xl tabular-nums text-white/80" aria-label="Current time">{{ clock }}</span>
            </div>
        </header>

        <!-- No top padding: pages with a full-bleed hero start at y=0 behind the top bar. -->
        <main class="flex-1" style="padding-bottom: var(--kiosk-dock-h)">
            <slot />
        </main>

        <!-- Bottom dock: big touch targets, scrolls sideways if they don't all fit. -->
        <nav class="fixed bottom-0 inset-x-0 z-50 flex items-center backdrop-blur-md border-t border-white/10"
            style="height: var(--kiosk-dock-h); background: rgba(10, 12, 16, 0.88)" aria-label="Main navigation">
            <div class="no-scrollbar w-full overflow-x-auto snap-x snap-proximity px-4">
                <ul class="flex gap-3 w-max mx-auto">
                    <li v-for="link in visibleItems(config)" :key="link.href" class="snap-center">
                        <Link :href="link.href" :aria-current="isActive(link.href) ? 'page' : undefined"
                            class="flex items-center justify-center h-[3rem] min-w-[5rem] px-4 rounded-full text-lg uppercase tracking-wide border transition-transform duration-150 active:scale-95"
                            :class="isActive(link.href)
                                    ? 'border-transparent text-slate-900'
                                    : 'border-white/25 text-white bg-white/10'
                                " :style="isActive(link.href) ? { background: 'var(--luxury-champagne)' } : undefined">
                            {{ link.label }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</template>
