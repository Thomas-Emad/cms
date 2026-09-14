<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';

defineProps<{
    hotel?: Hotel;
}>();

/**
 * Nav starts transparent (so it can sit over a full-bleed hero) and picks
 * up a solid/blurred background once the page has scrolled past the
 * hero. `isScrolled` also controls text color, since transparent-over-
 * imagery needs light text but solid-over-content needs dark text.
 */
const isScrolled = ref(false);
const mobileMenuOpen = ref(false);

const NAV_LINKS = [
    { href: '/', label: 'Home' },
    { href: '/restaurants', label: 'Dining' },
    { href: '/facilities', label: 'Facilities' },
    { href: '/services', label: 'Services' },
    { href: '/events', label: 'Events' },
    { href: '/offers', label: 'Offers' },
    { href: '/experiences', label: 'Experiences' },
];

function onScroll() {
    isScrolled.value = window.scrollY > 48;
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});
onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white text-slate-900" style="font-family: var(--font-sans);">
        <!--
      Guest navigation.

      Transparent + light text while at the top of a page (so it reads
      as part of a hero scene rather than a generic navbar), then fades
      to a solid/blurred bar with dark text once scrolled. This works
      whether or not the page actually has a hero - pages without one
      just get the solid state immediately on next scroll frame.
    -->
        <header
            class="fixed top-0 inset-x-0 z-50 transition-colors duration-500"
            :class="isScrolled ? 'bg-white/90 backdrop-blur-md border-b border-slate-100 text-slate-900' : 'bg-transparent text-white'"
        >
            <div class="mx-auto max-w-7xl px-6 lg:px-10 h-16 lg:h-20 flex items-center justify-between">
                <Link
                    href="/"
                    class="text-xl lg:text-2xl tracking-tight"
                    :style="{ fontFamily: 'var(--font-display)', color: isScrolled ? 'var(--color-primary, #1F4B5A)' : undefined }"
                >
                    {{ hotel?.name ?? 'Hotel' }}
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden lg:flex items-center gap-8 text-sm tracking-wide uppercase">
                    <Link
                        v-for="link in NAV_LINKS"
                        :key="link.href"
                        :href="link.href"
                        class="relative py-2 opacity-90 hover:opacity-100 transition-opacity group"
                    >
                        {{ link.label }}
                        <span
                            class="absolute left-0 -bottom-0.5 h-px w-0 group-hover:w-full transition-all duration-300 ease-out"
                            :style="{ background: isScrolled ? 'var(--color-primary, #1F4B5A)' : 'white' }"
                        />
                    </Link>
                </nav>

                <!-- Mobile toggle -->
                <button
                    type="button"
                    class="lg:hidden p-2 -mr-2"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle menu"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <span
                        class="block w-6 h-px mb-1.5 transition-transform"
                        :class="{ 'rotate-45 translate-y-[3px]': mobileMenuOpen }"
                        :style="{ background: isScrolled || mobileMenuOpen ? 'currentColor' : 'white' }"
                    />
                    <span
                        class="block w-6 h-px mb-1.5"
                        :class="{ 'opacity-0': mobileMenuOpen }"
                        :style="{ background: isScrolled ? 'currentColor' : 'white' }"
                    />
                    <span
                        class="block w-6 h-px transition-transform"
                        :class="{ '-rotate-45 -translate-y-[6px]': mobileMenuOpen }"
                        :style="{ background: isScrolled || mobileMenuOpen ? 'currentColor' : 'white' }"
                    />
                </button>
            </div>

            <!-- Mobile drawer -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <nav v-if="mobileMenuOpen" class="lg:hidden bg-white text-slate-900 border-t border-slate-100 shadow-lg">
                    <Link
                        v-for="link in NAV_LINKS"
                        :key="link.href"
                        :href="link.href"
                        class="block px-6 py-4 text-sm border-b border-slate-50 last:border-0"
                        @click="mobileMenuOpen = false"
                    >
                        {{ link.label }}
                    </Link>
                </nav>
            </Transition>
        </header>

        <!--
      No top padding here: pages with a full-bleed hero want content
      starting at y=0 behind the transparent nav. Pages without a hero
      should give their first section top padding themselves
      (e.g. pt-24 lg:pt-28) so content doesn't sit under the nav bar.
    -->
        <main class="flex-1">
            <slot />
        </main>

        <footer class="mt-auto border-t border-slate-100" style="background: #0a0c10; color: rgba(255,255,255,0.7)">
            <div class="mx-auto max-w-7xl px-6 lg:px-10 py-14 lg:py-20 grid grid-cols-1 lg:grid-cols-4 gap-10">
                <div class="lg:col-span-2">
                    <p class="text-2xl text-white" style="font-family: var(--font-display)">
                        {{ hotel?.name ?? 'Hotel' }}
                    </p>
                    <p class="mt-3 max-w-sm text-sm leading-relaxed">
                        An experience beyond the ordinary — crafted spaces, considered service, and moments worth
                        staying for.
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-white/50 mb-4">Explore</p>
                    <ul class="space-y-2 text-sm">
                        <li v-for="link in NAV_LINKS.slice(1)" :key="link.href">
                            <Link :href="link.href" class="hover:text-white transition-colors">{{ link.label }}</Link>
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-white/50 mb-4">Connect</p>
                    <ul class="space-y-2 text-sm">
                        <li>Reservations</li>
                        <li>Contact</li>
                        <li>Careers</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 px-6 lg:px-10 py-6 text-xs text-white/40">
                © {{ new Date().getFullYear() }} {{ hotel?.name ?? 'Hotel' }}. All rights reserved.
            </div>
        </footer>
    </div>
</template>
