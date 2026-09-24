<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Hotel } from '@/types/hotel';
import { useI18n } from '@/i18n';

withDefaults(
    defineProps<{
        hotel?: Hotel;
    }>(),
    {
        hotel: undefined,
    }
);

const { t, locale } = useI18n();
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="border-t border-white/10 text-white/80 pt-14 pb-8 transition-colors duration-200"
        style="background: var(--footer-bg, #0b0e13)"
    >
        <div class="mx-auto max-w-7xl px-6 lg:px-10">
            <!-- Top Grid: Brand & Navigation -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-white/10">
                <!-- Brand & Overview -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="h-3 w-3 rounded-full shrink-0"
                            style="background-color: var(--color-primary, #059669)"
                        />
                        <span class="text-2xl font-semibold tracking-tight text-white" style="font-family: var(--font-display)">
                            {{ hotel?.name ?? 'Grand Horizon Resort' }}
                        </span>
                    </div>

                    <p class="text-sm text-white/60 max-w-md leading-relaxed">
                        {{ locale === 'ar'
                            ? 'وجهة الضيافة الفاخرة التي تجمع بين الراحة الاستثنائية والخدمة الراقية لتقديم إقامة لا تُنسى لجميع النزلاء.'
                            : 'An extraordinary sanctuary of hospitality, offering bespoke accommodations, exquisite dining, and world-class amenities for unforgettable stays.'
                        }}
                    </p>

                    <div class="pt-2 flex items-center gap-2 text-xs text-white/70">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/15">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                            {{ locale === 'ar' ? 'خدمة الغرف والاستقبال ٢٤/٧' : '24/7 Front Desk & Concierge' }}
                        </span>
                    </div>
                </div>

                <!-- Navigation Column 1: Stay & Facilities -->
                <div class="space-y-3">
                    <h4 class="text-xs uppercase tracking-wider text-white font-semibold">
                        {{ locale === 'ar' ? 'الإقامة والمرافق' : 'Stay & Facilities' }}
                    </h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link href="/rooms" class="hover:text-white transition-colors">
                                {{ t('nav.rooms', undefined, 'Rooms & Suites') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/facilities" class="hover:text-white transition-colors">
                                {{ t('nav.facilities', undefined, 'Facilities & Spa') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/meeting-rooms" class="hover:text-white transition-colors">
                                {{ t('nav.meeting_rooms', undefined, 'Meeting & Events') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/map" class="hover:text-white transition-colors">
                                {{ t('nav.hotel_map', undefined, 'Resort Map') }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Navigation Column 2: Experiences & Dining -->
                <div class="space-y-3">
                    <h4 class="text-xs uppercase tracking-wider text-white font-semibold">
                        {{ locale === 'ar' ? 'المطاعم والخدمات' : 'Dining & Services' }}
                    </h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link href="/restaurants" class="hover:text-white transition-colors">
                                {{ t('nav.restaurants', undefined, 'Restaurants & Dining') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/services" class="hover:text-white transition-colors">
                                {{ t('nav.services', undefined, 'Guest Services') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/offers" class="hover:text-white transition-colors">
                                {{ t('nav.offers', undefined, 'Special Offers') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/weather" class="hover:text-white transition-colors">
                                {{ t('nav.weather', undefined, 'Weather Forecast') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/branches" class="hover:text-white transition-colors">
                                {{ t('nav.branches', undefined, 'Our Branches') }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Contact & Location -->
                <div class="space-y-3">
                    <h4 class="text-xs uppercase tracking-wider text-white font-semibold">
                        {{ locale === 'ar' ? 'الاتصال والموقع' : 'Contact & Location' }}
                    </h4>
                    <div class="space-y-2.5 text-xs text-white/70">
                        <div class="flex items-start gap-2">
                            <span class="text-white/40 shrink-0">📍</span>
                            <span>{{ hotel?.address ?? '1 Horizon Bay Drive, Coastal Boulevard' }}</span>
                        </div>
                        <div v-if="hotel?.contact_phone" class="flex items-center gap-2">
                            <span class="text-white/40 shrink-0">📞</span>
                            <a :href="`tel:${hotel.contact_phone}`" class="hover:text-white transition-colors font-mono">
                                {{ hotel.contact_phone }}
                            </a>
                        </div>
                        <div v-if="hotel?.contact_email" class="flex items-center gap-2">
                            <span class="text-white/40 shrink-0">✉️</span>
                            <a :href="`mailto:${hotel.contact_email}`" class="hover:text-white transition-colors truncate">
                                {{ hotel.contact_email }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Sub-bar: Copyright & Accent -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/50">
                <div class="flex items-center gap-2">
                    <span
                        class="h-2 w-2 rounded-full shrink-0"
                        style="background-color: var(--color-primary, #059669)"
                    />
                    <span>
                        © {{ currentYear }} {{ hotel?.name ?? 'Grand Horizon' }}. {{ locale === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                    </span>
                </div>

                <div class="flex items-center gap-6">
                    <Link href="/timing" class="hover:text-white transition-colors">
                        {{ t('nav.timing', undefined, 'Timing') }}
                    </Link>
                    <Link href="/short-calls" class="hover:text-white transition-colors">
                        {{ t('nav.short_calls', undefined, 'Short Calls') }}
                    </Link>
                    <a href="#top" class="hover:text-white transition-colors flex items-center gap-1">
                        <span>{{ locale === 'ar' ? 'العودة للأعلى' : 'Back to top' }}</span>
                        <span>↑</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
