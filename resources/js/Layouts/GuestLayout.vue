<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';

defineProps<{
    hotel?: Hotel;
}>();

/**
 * Hospitality-screen (touch, landscape) shell.
 *
 * Layout: slim dark top bar (hotel name + clock) and a large bottom dock of
 * pill buttons. Both are `fixed`, so every guest page keeps its own natural
 * document scroll (IntersectionObserver reveals, parallax on window scroll and
 * the existing page hero heights all keep working unchanged).
 *
 * Why the top bar is ALWAYS dark: the old bar switched between white-on-image
 * and dark-on-white depending on page content, which broke on pages without a
 * hero. A dark bar is legible over photos and over white pages, so no page
 * needs to opt in or out.
 */

const page = usePage();

// Each button opens its own guest page. Home is reached via the hotel name /
// "Home" button in the top bar, not the dock.
const NAV_LINKS = [
    { href: '/facilities', label: 'Facilities' },
    { href: '/timing', label: 'Timing' },
    { href: '/map', label: 'Map' },
    { href: '/short-calls', label: 'Short Calls' },
    { href: '/rooms', label: 'Rooms & Suites' },
    { href: '/gallery', label: 'Gallery' },
    { href: '/meeting-rooms', label: 'Meeting Room' },
];

function isActive(href: string): boolean {
    // Ignore query string so /events?page=2 still highlights Events.
    const path = page.url.split('?')[0];
    return href === '/' ? path === '/' : path === href || path.startsWith(href + '/');
}

/* ---------------------------------------------------------------- clock */
const onHome = computed(() => page.url.split('?')[0] === '/');

const now = ref(new Date());
const clock = computed(() =>
    now.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
);
let clockTimer: number | undefined;

/* ----------------------------------------------------------- idle reset */
// A shared screen must not stay on the last guest's page. After IDLE_MS with
// no touch, go back to the home page (or scroll to top if already there).
const IDLE_MS = 90_000;
let idleTimer: number | undefined;

function goHome() {
    const path = page.url.split('?')[0];
    if (path !== '/') {
        router.visit('/');
    } else if (window.scrollY > 0) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function resetIdle() {
    window.clearTimeout(idleTimer);
    idleTimer = window.setTimeout(goHome, IDLE_MS);
}

const IDLE_EVENTS = ['pointerdown', 'touchstart', 'scroll', 'keydown'] as const;

/* ---------------------------------------------------------- root scale */
// Sizes are in rem, so scaling the root font size scales the whole guest UI
// for the screen (1080p ~ 22px, 4K caps at 32px). Restored on unmount so the
// admin area is unaffected.
const previousFontSize = ref('');

onMounted(() => {
    previousFontSize.value = document.documentElement.style.fontSize;
    document.documentElement.style.fontSize = 'clamp(16px, 0.75vw + 8px, 32px)';

    clockTimer = window.setInterval(() => (now.value = new Date()), 15_000);

    IDLE_EVENTS.forEach((e) => window.addEventListener(e, resetIdle, { passive: true }));
    resetIdle();
});

onUnmounted(() => {
    document.documentElement.style.fontSize = previousFontSize.value;
    window.clearInterval(clockTimer);
    window.clearTimeout(idleTimer);
    IDLE_EVENTS.forEach((e) => window.removeEventListener(e, resetIdle));
});
</script>

<template>
    <div
        class="kiosk-root min-h-screen flex flex-col bg-white text-slate-900"
        style="font-family: var(--font-sans); --kiosk-topbar-h: 5rem; --kiosk-dock-h: 7.5rem"
        @contextmenu.prevent
    >
        <!-- Top bar: identity + time only. Navigation lives in the dock. -->
        <header
            class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-10 text-white backdrop-blur-md"
            style="height: var(--kiosk-topbar-h); background: rgba(10, 12, 16, 0.82)"
        >
            <Link href="/" class="text-2xl tracking-tight" style="font-family: var(--font-display)">
                {{ hotel?.name ?? 'Hotel' }}
            </Link>
            <div class="flex items-center gap-6">
                <Link
                    v-if="!onHome"
                    href="/"
                    class="flex items-center h-12 px-6 rounded-full border border-white/25 bg-white/10 text-base uppercase tracking-wider transition-transform duration-150 active:scale-95"
                >
                    ← Home
                </Link>
                <span class="text-xl tabular-nums text-white/80" aria-label="Current time">{{ clock }}</span>
            </div>
        </header>

        <!--
      No top padding: pages with a full-bleed hero start at y=0 behind the
      top bar; pages without one already pad their first block (pt-24) to
      clear it. Bottom padding keeps the last content clear of the dock.
    -->
        <main class="flex-1" style="padding-bottom: var(--kiosk-dock-h)">
            <slot />
        </main>

        <!-- Bottom dock: big touch targets, scrolls sideways if they don't all fit. -->
        <nav
            class="fixed bottom-0 inset-x-0 z-50 flex items-center backdrop-blur-md border-t border-white/10"
            style="height: var(--kiosk-dock-h); background: rgba(10, 12, 16, 0.88)"
            aria-label="Main navigation"
        >
            <div class="no-scrollbar w-full overflow-x-auto snap-x snap-proximity px-10">
                <ul class="flex gap-3 w-max mx-auto">
                    <li v-for="link in NAV_LINKS" :key="link.href" class="snap-center">
                        <Link
                            :href="link.href"
                            :aria-current="isActive(link.href) ? 'page' : undefined"
                            class="flex items-center justify-center h-[4.5rem] min-w-[7rem] px-6 rounded-full text-lg uppercase tracking-wide border transition-transform duration-150 active:scale-95"
                            :class="
                                isActive(link.href)
                                    ? 'border-transparent text-slate-900'
                                    : 'border-white/25 text-white bg-white/10'
                            "
                            :style="isActive(link.href) ? { background: 'var(--luxury-champagne)' } : undefined"
                        >
                            {{ link.label }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</template>
