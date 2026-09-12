<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';

defineProps<{
    hotel?: Hotel;
}>();

const showMore = ref(false);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white text-slate-900"
        style="font-family: var(--font-family, 'Inter'), sans-serif;">
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-100">
            <div class="mx-auto max-w-screen-sm px-4 py-3 flex items-center justify-between">
                <Link href="/" class="font-semibold tracking-tight text-lg"
                    style="color: var(--color-primary, #1F4B5A)">
                    {{ hotel?.name ?? 'Hotel' }}
                </Link>
                <!-- Phase 5: search icon opens global search overlay -->
                <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" aria-label="Search">
                    🔍
                </button>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <!--
      "More" popup menu covers the remaining guest routes (Services,
      Events, Offers, Experiences) that don't fit in a 4-icon bottom nav.
      A simple toggle, not a new component/library - reassess if this
      list grows much further.
    -->
        <div v-if="showMore" class="fixed inset-0 z-40 bg-black/20" @click="showMore = false">
            <div class="absolute bottom-14 left-0 right-0 mx-auto max-w-screen-sm bg-white rounded-t-xl shadow-lg p-2"
                @click.stop>
                <Link href="/services" class="block rounded-md px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">🛎️
                    Hotel Services</Link>
                <Link href="/events" class="block rounded-md px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">📅
                    Events</Link>
                <Link href="/offers" class="block rounded-md px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">🏷️
                    Offers</Link>
                <Link href="/experiences" class="block rounded-md px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">🧘
                    Experiences</Link>
            </div>
        </div>

        <nav class="sticky bottom-0 z-40 border-t border-slate-100 bg-white">
            <div class="mx-auto max-w-screen-sm px-4 py-2 flex justify-around text-xs text-slate-500">
                <Link href="/" class="flex flex-col items-center gap-0.5 py-1">
                    <span>🏠</span><span>Home</span>
                </Link>
                <Link href="/restaurants" class="flex flex-col items-center gap-0.5 py-1">
                    <span>🍽️</span><span>Dine</span>
                </Link>
                <Link href="/facilities" class="flex flex-col items-center gap-0.5 py-1">
                    <span>💆</span><span>Facilities</span>
                </Link>
                <button type="button" class="flex flex-col items-center gap-0.5 py-1" @click="showMore = !showMore">
                    <span>☰</span><span>More</span>
                </button>
            </div>
        </nav>
    </div>
</template>
