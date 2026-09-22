import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

/**
 * Behaviour every guest-screen template shares (moved out of GuestLayout unchanged):
 *  - a clock,
 *  - "which menu item is current",
 *  - idle reset (a shared screen must not stay on the last guest's page),
 *  - root font-size scaling for the screen (restored on unmount so the admin area is unaffected).
 */
export const IDLE_MS = 90_000;

export function useKioskShell(opts: { skipFontScale?: boolean } = {}) {
    const page = usePage();
    const path = () => page.url.split('?')[0];

    const onHome = computed(() => path() === '/');

    function isActive(href: string): boolean {
        // Ignore query string so /events?page=2 still highlights Events.
        const p = path();
        return href === '/' ? p === '/' : p === href || p.startsWith(href + '/');
    }

    const now = ref(new Date());
    const clock = computed(() => now.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
    let clockTimer: number | undefined;

    let idleTimer: number | undefined;
    function goHome() {
        if (path() !== '/') router.visit('/');
        else if (window.scrollY > 0) window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function resetIdle() {
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(goHome, IDLE_MS);
    }
    const IDLE_EVENTS = ['pointerdown', 'touchstart', 'scroll', 'keydown'] as const;

    // opts.skipFontScale: the admin's live layout preview mounts this same
    // shell inline (not in an iframe), so document.documentElement is the
    // REAL page root - scaling it there would blow up the whole dashboard's
    // font size, not just the small preview box. Real guest screens keep
    // the scaling; the preview opts out of it.
    let previousFontSize = '';
    onMounted(() => {
        if (!opts.skipFontScale) {
            previousFontSize = document.documentElement.style.fontSize;
            document.documentElement.style.fontSize = 'clamp(16px, 0.75vw + 8px, 32px)';
        }
        clockTimer = window.setInterval(() => (now.value = new Date()), 15_000);
        IDLE_EVENTS.forEach((e) => window.addEventListener(e, resetIdle, { passive: true }));
        resetIdle();
    });
    onUnmounted(() => {
        if (!opts.skipFontScale) document.documentElement.style.fontSize = previousFontSize;
        window.clearInterval(clockTimer);
        window.clearTimeout(idleTimer);
        IDLE_EVENTS.forEach((e) => window.removeEventListener(e, resetIdle));
    });

    return { page, onHome, isActive, clock };
}
