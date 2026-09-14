<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { Offer } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ offer: Offer }>();
</script>

<template>
    <div>
        <section class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <div class="absolute inset-0">
                <img v-if="offer.cover_image_url" :src="offer.cover_image_url" :alt="offer.title" class="w-full h-full object-cover" />
                <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
            </div>
            <div class="relative z-10 w-full px-6 lg:px-10 pb-14">
                <div class="mx-auto max-w-7xl">
                    <h1 class="text-white text-5xl lg:text-6xl" style="font-family: var(--font-display)">{{ offer.title }}</h1>
                    <p v-if="offer.price" class="mt-3 text-white/85 text-lg">
                        From ${{ offer.price }}<span v-if="offer.discount"> · Save {{ offer.discount }}%</span>
                    </p>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-3xl px-6 lg:px-10 py-16 lg:py-24">
            <p v-if="offer.description" class="text-lg leading-relaxed text-slate-600 whitespace-pre-line">{{ offer.description }}</p>
            <p v-if="offer.valid_until" class="mt-4 text-xs uppercase tracking-wide text-slate-400">Valid until {{ offer.valid_until }}</p>
            <a
                v-if="offer.booking_url"
                :href="offer.booking_url"
                class="group mt-8 inline-flex items-center gap-2 text-sm uppercase tracking-wide"
                style="color: var(--color-primary, #1f4b5a)"
            >
                <span class="border-b border-current/40 pb-1 group-hover:border-current transition-colors">Book this offer</span>
                <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
            </a>
        </div>
    </div>
</template>
