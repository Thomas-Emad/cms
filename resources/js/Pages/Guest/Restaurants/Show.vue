<script setup lang="ts">
import GuestShell from '@/Layouts/GuestShell.vue';
import type { Restaurant } from '@/types/restaurant';

defineOptions({ layout: GuestShell });

defineProps<{
    restaurant: Restaurant;
}>();
</script>

<template>
    <div>
        <!-- Fullscreen hero — matches RestaurantHeroSection's language even
         though this legacy fallback page has no Builder sections. -->
        <section class="relative w-full h-[70vh] min-h-[480px] overflow-hidden flex items-end" style="background: #1c1f26">
            <div class="absolute inset-0">
                <img
                    v-if="restaurant.cover_image_url"
                    :src="restaurant.cover_image_url"
                    :alt="restaurant.name"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
            </div>
            <div class="relative z-10 w-full px-6 lg:px-10 pb-14">
                <div class="mx-auto max-w-7xl">
                    <p v-if="restaurant.cuisine" class="text-white/70 text-xs uppercase tracking-[0.2em] mb-2">
                        {{ restaurant.cuisine }}
                    </p>
                    <h1 class="text-white text-5xl lg:text-6xl" style="font-family: var(--font-display)">
                        {{ restaurant.name }}
                    </h1>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-6 lg:px-10 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <p
                    v-if="restaurant.description"
                    class="lg:col-span-7 text-xl leading-relaxed text-slate-700"
                    style="font-family: var(--font-display)"
                >
                    {{ restaurant.description }}
                </p>
                <div class="lg:col-span-4 lg:col-start-9 space-y-5 text-sm">
                    <div v-if="restaurant.dress_code">
                        <p class="text-slate-400 uppercase tracking-wide text-xs">{{ $t('restaurants.dress_code') }}</p>
                        <p class="mt-1 text-slate-700">{{ restaurant.dress_code }}</p>
                    </div>
                    <div v-if="restaurant.reservation_url">
                        <p class="text-slate-400 uppercase tracking-wide text-xs">{{ $t('restaurants.reservations') }}</p>
                        <a :href="restaurant.reservation_url" class="mt-1 inline-block text-slate-700 border-b border-slate-300 hover:border-slate-700 transition-colors">
                            {{ $t('common.book_table') }}
                        </a>
                    </div>
                </div>
            </div>

            <div v-if="restaurant.active_menu" class="mt-16 lg:mt-24">
                <h2 class="text-2xl lg:text-3xl mb-8 text-center" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">
                    {{ $t('restaurants.menu') }}
                </h2>
                <div class="mx-auto max-w-2xl space-y-10">
                    <div v-for="category in restaurant.active_menu.categories" :key="category.id">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400 mb-3">
                            {{ category.name }}
                        </h3>
                        <div class="space-y-4">
                            <div v-for="item in category.items" :key="item.id" class="flex items-baseline gap-4">
                                <div class="min-w-0">
                                    <p style="font-family: var(--font-display)" class="text-lg text-slate-800">{{ item.name }}</p>
                                    <p v-if="item.description" class="text-sm text-slate-500 mt-0.5">{{ item.description }}</p>
                                </div>
                                <div class="flex-1 border-b border-dotted border-slate-200 self-center translate-y-[-2px]" />
                                <span class="shrink-0" style="color: var(--color-primary, #1f4b5a)">${{ item.price }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
