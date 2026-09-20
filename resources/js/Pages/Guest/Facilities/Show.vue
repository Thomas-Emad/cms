<script setup lang="ts">
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { Facility } from '@/types/facility';
import { useParallax, vRevealMount } from '@/lib/motion';

defineOptions({ layout: GuestLayout });

defineProps<{
    facility: Facility;
}>();

const heroRef = ref<HTMLElement | null>(null);
const { style: parallaxStyle } = useParallax(heroRef, { strength: 0.12 });
</script>

<template>
    <div>
        <section ref="heroRef" class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <div class="absolute inset-0">
                <img
                    v-if="facility.cover_image_url"
                    :src="facility.cover_image_url"
                    :alt="facility.name"
                    class="reveal-scale w-full h-full object-cover"
                    :style="parallaxStyle()"
                    v-reveal-mount="{ delay: 0 }"
                />
                <div class="absolute inset-0 reveal" style="background: var(--atmosphere-gradient)" v-reveal-mount="{ delay: 150 }" />
            </div>
            <div class="relative z-10 w-full px-6 lg:px-10 pb-14">
                <div class="mx-auto max-w-7xl">
                    <p
                        v-if="facility.category"
                        class="reveal text-white/70 text-xs uppercase tracking-[0.2em] mb-2"
                        v-reveal-mount="{ delay: 300 }"
                    >
                        {{ facility.category }}
                    </p>
                    <div class="reveal-mask" v-reveal-mount="{ delay: 460 }">
                        <h1 class="reveal-mask-inner text-white text-5xl lg:text-6xl" style="font-family: var(--font-display)">
                            {{ facility.name }}
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-6 lg:px-10 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <p
                    v-if="facility.description"
                    class="reveal lg:col-span-7 text-xl leading-relaxed text-slate-700 whitespace-pre-line"
                    style="font-family: var(--font-display)"
                    v-reveal
                >
                    {{ facility.description }}
                </p>

                <div class="reveal lg:col-span-4 lg:col-start-9 space-y-5 text-sm" v-reveal="{ delay: 100 }">
                    <div v-if="facility.building">
                        <p class="text-slate-400 uppercase tracking-wide text-xs">Location</p>
                        <p class="mt-1 text-slate-700">
                            {{ [facility.building, facility.floor, facility.wing].filter(Boolean).join(', ') }}
                        </p>
                    </div>
                    <div v-if="facility.phone">
                        <p class="text-slate-400 uppercase tracking-wide text-xs">Phone</p>
                        <p class="mt-1 text-slate-700">{{ facility.phone }}</p>
                    </div>
                </div>
            </div>

            <ul v-if="facility.amenities?.length" class="mt-10 flex flex-wrap gap-2">
                <li
                    v-for="(amenity, i) in facility.amenities"
                    :key="amenity"
                    class="reveal rounded-full border border-slate-200 px-4 py-1.5 text-xs uppercase tracking-wide text-slate-600"
                    v-reveal="{ delay: i * 60 }"
                >
                    {{ amenity }}
                </li>
            </ul>
        </div>
    </div>
</template>
