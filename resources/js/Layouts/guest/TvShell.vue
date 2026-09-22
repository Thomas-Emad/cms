<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { Hotel } from '@/types/hotel';
import { type GuestLayoutConfig, TILE_DIMENSIONS, readableOn, visibleItems } from './shellConfig';
import { useKioskShell } from './useKioskShell';

/**
 * "Smart TV" template: like a TV launcher.
 *  - HOME is one full screen that never scrolls (unless the admin turns the lock off): the page's
 *    picture/slideshow fills the whole screen, the hotel name sits top-left, and a row of big
 *    coloured tiles runs along the bottom.
 *  - Every OTHER page scrolls normally, with a slimmer top bar and a compact tile row.
 * Arrow keys / a TV remote move between tiles; Enter opens one.
 */
const props = defineProps<{ hotel?: Hotel; config: GuestLayoutConfig; preview?: boolean }>();

const { onHome, isActive, clock } = useKioskShell({ skipFontScale: props.preview });

const items = computed(() => visibleItems(props.config));
const dims = computed(() => TILE_DIMENSIONS[props.config.tile_size]);
const locked = computed(() => onHome.value && props.config.lock_home_scroll);

/* ---- no scrolling on the locked home screen ---- */
let prevHtml = '';
let prevBody = '';
watch(
    locked,
    (on) => {
        if (on) {
            prevHtml = document.documentElement.style.overflow;
            prevBody = document.body.style.overflow;
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        } else {
            document.documentElement.style.overflow = prevHtml;
            document.body.style.overflow = prevBody;
        }
    },
    { immediate: true },
);
onBeforeUnmount(() => {
    if (locked.value) {
        document.documentElement.style.overflow = prevHtml;
        document.body.style.overflow = prevBody;
    }
});

/* ---- remote / keyboard: Left and Right move along the tiles ---- */
const rail = ref<HTMLElement | null>(null);
function onKey(e: KeyboardEvent) {
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
    const el = document.activeElement as HTMLElement | null;
    if (el && ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName)) return;
    const tiles = [...(rail.value?.querySelectorAll<HTMLElement>('a') ?? [])];
    if (!tiles.length) return;
    const inRail = !!el && tiles.includes(el);
    if (!inRail && el && el !== document.body) return; // something else (e.g. a map pin) owns the arrows
    const current = inRail ? tiles.indexOf(el!) : tiles.findIndex((t) => t.getAttribute('aria-current') === 'page');
    const next = Math.max(0, Math.min(tiles.length - 1, (current < 0 ? 0 : current) + (inRail || current >= 0 ? (e.key === 'ArrowRight' ? 1 : -1) : 0)));
    e.preventDefault();
    tiles[next].focus();
    tiles[next].scrollIntoView?.({ inline: 'center', block: 'nearest', behavior: 'smooth' });
}
onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));

const tileStyle = (color: string | null) => ({ background: color ?? '#1f2937', color: readableOn(color ?? '#1f2937') });
</script>

<template>
    <div
        class="kiosk-root tv-root relative flex flex-col bg-white text-slate-900"
        :class="locked ? 'h-dvh overflow-hidden' : 'min-h-screen'"
        :style="{
            fontFamily: 'var(--font-sans)',
            '--tv-tile-w': `${dims.w}rem`,
            '--tv-tile-h': onHome ? `${dims.h}rem` : `${dims.h * 0.8}rem`,
            // On home the picture runs behind everything; other pages keep clear of the bars.
            '--kiosk-topbar-h': onHome ? '0px' : '5rem',
            '--kiosk-dock-h': onHome ? '0px' : 'calc(var(--tv-tile-h) + 2rem)',
            // Unlike --kiosk-dock-h, this always reflects the tile row's real
            // on-screen footprint - including on home, where dock-h is
            // zeroed out on purpose for the full-bleed hero. Sections like
            // HeroSection use this to keep their own content clear of the
            // fixed dock instead of relying on a guessed fixed padding.
            '--kiosk-dock-overlay-h': onHome ? 'calc(var(--tv-tile-h) + 3rem)' : '0px',
        }"
        data-testid="tv-shell"
        @contextmenu.prevent
    >
        <!-- HOME: brand top-left, clock top-right, over the picture -->
        <header v-if="onHome" class="pointer-events-none absolute inset-x-0 top-0 z-40 flex items-start justify-between px-12 pb-16 pt-8 text-white" style="background: linear-gradient(to bottom, rgba(0,0,0,0.6), transparent)" data-testid="tv-brand">
            <div class="pointer-events-auto">
                <Link href="/" class="block text-4xl tracking-tight" style="font-family: var(--font-display)">{{ hotel?.name ?? 'Hotel' }}</Link>
                <p v-if="config.tagline" class="mt-1 text-xl text-white/85" data-testid="tv-tagline">{{ config.tagline }}</p>
            </div>
            <span v-if="config.show_clock" class="text-3xl tabular-nums text-white/90" aria-label="Current time">{{ clock }}</span>
        </header>

        <!-- OTHER PAGES: slim bar -->
        <header v-else class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-10 text-white backdrop-blur-md" style="height: var(--kiosk-topbar-h); background: rgba(10, 12, 16, 0.82)">
            <Link href="/" class="text-2xl tracking-tight" style="font-family: var(--font-display)">{{ hotel?.name ?? 'Hotel' }}</Link>
            <div class="flex items-center gap-6">
                <Link href="/" class="flex h-9 items-center rounded-full border border-white/25 bg-white/10 px-6 text-base uppercase tracking-wider transition-transform duration-150 active:scale-95">← Home</Link>
                <span v-if="config.show_clock" class="text-xl tabular-nums text-white/80" aria-label="Current time">{{ clock }}</span>
            </div>
        </header>

        <main class="min-h-0 flex-1" :class="{ 'overflow-hidden': locked }" style="padding-bottom: var(--kiosk-dock-h)">
            <slot />
        </main>

        <!-- big line over the picture, just above the tiles -->
        <p v-if="onHome && config.headline" class="pointer-events-none absolute left-12 z-40 max-w-[60%] text-4xl leading-tight text-white" style="bottom: calc(var(--tv-tile-h) + 3.5rem); font-family: var(--font-display); text-shadow: 0 2px 16px rgba(0,0,0,0.6)" data-testid="tv-headline">
            {{ config.headline }}
        </p>

        <!-- the tile row -->
        <nav
            class="fixed inset-x-0 bottom-0 z-50 flex items-center"
            :style="{ height: onHome ? 'calc(var(--tv-tile-h) + 3rem)' : 'var(--kiosk-dock-h)', background: onHome ? 'linear-gradient(to top, rgba(0,0,0,0.78), transparent)' : 'rgba(10, 12, 16, 0.9)' }"
            aria-label="Main navigation"
        >
            <ul ref="rail" class="no-scrollbar flex w-full items-center gap-4 overflow-x-auto px-12 py-3" data-testid="tv-rail">
                <!-- Home tile: small square with the four-squares glyph, like a TV's "Apps" -->
                <li class="shrink-0">
                    <Link href="/" :aria-current="onHome ? 'page' : undefined" aria-label="Home" class="tv-tile flex flex-col items-center justify-center gap-1 rounded-2xl bg-white/15 text-white backdrop-blur-md" :class="{ 'tv-tile--active': onHome }" :style="{ width: 'var(--tv-tile-h)', height: 'var(--tv-tile-h)' }" data-testid="tile-home">
                        <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2" /><rect x="13" y="3" width="8" height="8" rx="2" /><rect x="3" y="13" width="8" height="8" rx="2" /><rect x="13" y="13" width="8" height="8" rx="2" /></svg>
                        <span class="text-sm uppercase tracking-wide">Home</span>
                    </Link>
                </li>
                <li v-for="it in items" :key="it.href" class="shrink-0">
                    <Link
                        :href="it.href"
                        :aria-current="isActive(it.href) ? 'page' : undefined"
                        class="tv-tile flex flex-col items-center justify-center gap-1 rounded-2xl font-semibold uppercase tracking-wide"
                        :class="{ 'tv-tile--active': isActive(it.href) }"
                        :style="{ width: 'var(--tv-tile-w)', height: 'var(--tv-tile-h)', ...tileStyle(it.color) }"
                        data-testid="tv-tile"
                    >
                        <span v-if="it.icon" class="text-3xl leading-none" aria-hidden="true">{{ it.icon }}</span>
                        <span :class="it.icon ? 'text-base' : 'text-xl'">{{ it.label }}</span>
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
