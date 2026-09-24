<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

defineProps<{
    hotel?: Hotel;
}>();

/**
 * Samsung-Smart-TV-style guest shell: the counterpart to GuestLayout.vue's
 * hospitality-screen (dark top bar + bottom dock) shell. Picked per-hotel
 * via HotelSettings.guest_view - see GuestShell.vue, which is the layout
 * every guest page actually declares; it renders THIS or GuestLayout based
 * on that setting.
 *
 * Deliberately has no persistent top bar or bottom dock: a "TV home
 * screen" page (the app-launcher section) drives its own navigation via
 * its own tiles, the way a real Smart TV home screen does. This shell
 * only supplies the two things every guest page still needs regardless
 * of content: the idle-reset-to-home behavior (shared screen, must not
 * stay on the last guest's page) and the root font scaling for a
 * landscape TV-distance display. A small unobtrusive clock sits in the
 * corner since there's no top bar to put it in otherwise.
 */

const page = usePage();
const onHome = computed(() => page.url.split('?')[0] === '/');

const now = ref(new Date());
const clock = computed(() => now.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
let clockTimer: number | undefined;

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
    <div class="kiosk-root min-h-screen bg-black text-white" style="font-family: var(--font-sans); --kiosk-topbar-h: 0px; --kiosk-dock-h: 0px"
        @contextmenu.prevent>
        <!-- Small always-on-top clock, corner-anchored - the only persistent
             chrome. No back/home button here by design: on the TV shell,
             "home" is just another tile a page provides, same as a real
             Smart TV remote's dedicated Home button rather than an
             on-screen one. -->
        <div v-if="!onHome" class="fixed top-4 right-6 rtl:right-auto rtl:left-6 z-50 flex items-center gap-4 text-sm tabular-nums text-white/70" aria-label="Current time">
            <LanguageSwitcher variant="guest" />
            <span>{{ clock }}</span>
        </div>

        <main class="min-h-screen">
            <slot />
        </main>
    </div>
</template>
