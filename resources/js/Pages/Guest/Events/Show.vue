<script setup lang="ts">
import { ref } from 'vue';
import GuestShell from '@/Layouts/GuestShell.vue';
import type { HotelEvent } from '@/types/content';
import { useParallax, vRevealMount } from '@/lib/motion';
import { formatDate } from '@/lib/formatters';

defineOptions({ layout: GuestShell });

defineProps<{ event: HotelEvent }>();

const heroRef = ref<HTMLElement | null>(null);
const { style: parallaxStyle } = useParallax(heroRef, { strength: 0.12 });
</script>

<template>
    <div>
        <section ref="heroRef" class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <div class="absolute inset-0">
                <img
                    v-if="event.cover_image_url"
                    :src="event.cover_image_url"
                    :alt="event.title"
                    class="reveal-scale w-full h-full object-cover"
                    :style="parallaxStyle()"
                    v-reveal-mount="{ delay: 0 }"
                />
                <div class="absolute inset-0 reveal" style="background: var(--atmosphere-gradient)" v-reveal-mount="{ delay: 150 }" />
            </div>
            <div class="relative z-10 w-full px-6 lg:px-10 pb-14">
                <div class="mx-auto max-w-7xl">
                    <p class="reveal text-white/70 text-xs uppercase tracking-[0.2em] mb-2" v-reveal-mount="{ delay: 300 }">
                        {{ formatDate(event.start_date) }}<span v-if="event.start_time"> · {{ event.start_time }}</span>
                    </p>
                    <div class="reveal-mask" v-reveal-mount="{ delay: 460 }">
                        <h1 class="reveal-mask-inner text-white text-5xl lg:text-6xl" style="font-family: var(--font-display)">
                            {{ event.title }}
                        </h1>
                    </div>
                    <p v-if="event.location" class="reveal mt-3 text-white/85 text-lg" v-reveal-mount="{ delay: 640 }">
                        {{ event.location }}
                    </p>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-3xl px-6 lg:px-10 py-16 lg:py-24">
            <p v-if="event.description" class="reveal text-lg leading-relaxed text-slate-600 whitespace-pre-line" v-reveal>
                {{ event.description }}
            </p>
            <a
                v-if="event.booking_required && event.booking_url"
                :href="event.booking_url"
                class="reveal group mt-8 inline-flex items-center gap-2 text-sm uppercase tracking-wide"
                style="color: var(--color-primary, #1f4b5a)"
                v-reveal="{ delay: 100 }"
            >
                <span class="border-b border-current/40 pb-1 group-hover:border-current transition-colors">{{ $t('common.book_now') }}</span>
                <span class="inline-block transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180">→</span>
            </a>
        </div>
    </div>
</template>
