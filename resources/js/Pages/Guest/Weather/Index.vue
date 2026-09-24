<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GuestShell from '@/Layouts/GuestShell.vue';
import { useI18n } from '@/i18n';

defineOptions({ layout: GuestShell });

export interface WeatherDay {
    date: string;
    day_name: string;
    day_number: string;
    month_name: string;
    weather_code: number;
    condition_key: string;
    icon: string;
    temp_max: number;
    temp_min: number;
    apparent_temp_max: number;
    apparent_temp_min: number;
    precipitation: number;
    wind_speed: number;
}

export interface WeatherBranch {
    id: string;
    name: string;
    city: string;
    is_default: boolean;
}

export interface WeatherPayload {
    status: 'ok' | 'error';
    location: {
        city: string;
        branch_name?: string;
        latitude: number;
        longitude: number;
        timezone: string;
    };
    branches?: WeatherBranch[];
    current_branch_id?: string;
    units: {
        temperature: string;
        precipitation: string;
        wind: string;
    };
    days: WeatherDay[];
    error?: string;
}

const props = defineProps<{
    weather: WeatherPayload;
    title?: string;
    subtitle?: string;
}>();

const { t, locale, isRtl } = useI18n();

const isRetrying = ref(false);
const isSwitchingBranch = ref(false);

function retry() {
    isRetrying.value = true;
    router.reload({
        onFinish: () => {
            isRetrying.value = false;
        },
    });
}

function selectBranch(branchId: string) {
    if (branchId === props.weather?.current_branch_id) return;
    isSwitchingBranch.value = true;
    router.get(
        '/weather',
        { branch: branchId },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isSwitchingBranch.value = false;
            },
        },
    );
}

const hasDays = computed(() => props.weather?.status === 'ok' && Array.isArray(props.weather.days) && props.weather.days.length > 0);

// Most recent day from the last 5 days
const latestDay = computed<WeatherDay | null>(() => {
    if (!hasDays.value) return null;
    return props.weather.days[props.weather.days.length - 1];
});

/**
 * Format date nicely according to the active locale (English or Arabic).
 */
function formatLocalizedDate(dateStr: string): string {
    try {
        const d = new Date(dateStr + 'T12:00:00');
        const loc = locale.value === 'ar' ? 'ar-EG' : 'en-US';
        return new Intl.DateTimeFormat(loc, {
            weekday: 'long',
            day: 'numeric',
            month: 'short',
        }).format(d);
    } catch {
        return dateStr;
    }
}

/**
 * Format day of week according to the active locale.
 */
function formatDayName(dateStr: string): string {
    try {
        const d = new Date(dateStr + 'T12:00:00');
        const loc = locale.value === 'ar' ? 'ar-EG' : 'en-US';
        return new Intl.DateTimeFormat(loc, { weekday: 'long' }).format(d);
    } catch {
        return dateStr;
    }
}

/**
 * Format calendar date (e.g. 23 Sep / ٢٣ سبتمبر).
 */
function formatShortDate(dateStr: string): string {
    try {
        const d = new Date(dateStr + 'T12:00:00');
        const loc = locale.value === 'ar' ? 'ar-EG' : 'en-US';
        return new Intl.DateTimeFormat(loc, {
            day: 'numeric',
            month: 'short',
        }).format(d);
    } catch {
        return dateStr;
    }
}

function getConditionLabel(key: string): string {
    return t(`weather.conditions.${key}`, undefined, key.replace(/_/g, ' '));
}
</script>

<template>
    <div class="weather-page min-h-screen">
        <div class="mx-auto max-w-7xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
            <!-- Header section -->
            <div class="mb-8 lg:mb-10">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                            <span class="text-xs uppercase tracking-[0.2em] font-medium" style="color: var(--luxury-champagne, #c5a880)">
                                {{ t('weather.five_day_history', undefined, 'Past 5 Days Weather') }}
                            </span>
                        </div>
                        <h1 class="text-3xl lg:text-5xl tracking-tight text-slate-900" style="font-family: var(--font-display)">
                            {{ weather.location?.branch_name ? `${weather.location.branch_name} — ` : (weather.location?.city ? `${weather.location.city} — ` : '') }}{{ t('weather.title', undefined, 'Weather') }}
                        </h1>
                        <p class="mt-2 text-base lg:text-lg text-slate-500 max-w-2xl">
                            {{ t('weather.subtitle', undefined, 'Daily weather observations for the last 5 days') }}
                        </p>
                    </div>

                    <!-- Current active location chip -->
                    <div
                        v-if="weather.location?.city"
                        class="flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white/80 shadow-sm text-sm text-slate-600 backdrop-blur-sm"
                    >
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span class="font-medium">{{ weather.location.branch_name || weather.location.city }}</span>
                    </div>
                </div>

                <!-- Hotel Branch Switcher -->
                <div
                    v-if="weather.branches && weather.branches.length > 1"
                    class="mt-6 pt-6 border-t border-slate-200/70"
                >
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2" />
                            <path d="M9 22v-4h6v4" />
                            <path d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01" />
                        </svg>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ t('weather.branches', undefined, 'Hotel Branches') }}
                        </span>
                    </div>

                    <div class="no-scrollbar flex items-center gap-2.5 overflow-x-auto pb-1">
                        <button
                            v-for="branch in weather.branches"
                            :key="branch.id"
                            type="button"
                            class="group shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-medium transition-all duration-150 active:scale-95 cursor-pointer border"
                            :class="[
                                branch.id === weather.current_branch_id
                                    ? 'border-transparent text-white shadow-md'
                                    : 'border-slate-200 bg-white/80 hover:bg-white hover:border-slate-300 text-slate-700 shadow-xs',
                            ]"
                            :style="branch.id === weather.current_branch_id ? { background: 'var(--color-primary, #1f4b5a)' } : undefined"
                            @click="selectBranch(branch.id)"
                        >
                            <span
                                class="inline-block w-2 h-2 rounded-full transition-colors"
                                :class="branch.id === weather.current_branch_id ? 'bg-emerald-400' : 'bg-slate-300 group-hover:bg-slate-400'"
                            />
                            <span>{{ branch.name }}</span>
                            <span
                                v-if="branch.id === weather.current_branch_id"
                                class="text-xs opacity-75 ps-1"
                            >
                                ✓
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div
                v-if="weather.status === 'error' || !hasDays"
                class="rounded-2xl border border-red-100 bg-red-50/60 p-8 lg:p-12 text-center max-w-xl mx-auto shadow-sm"
            >
                <div class="mx-auto w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-2xl mb-4">
                    ⚠️
                </div>
                <h2 class="text-2xl font-semibold text-slate-800 mb-2">
                    {{ t('weather.error_title', undefined, 'Weather Unavailable') }}
                </h2>
                <p class="text-sm lg:text-base text-slate-600 mb-6">
                    {{ t('weather.error_message', undefined, 'Unable to load weather information at this moment. Please check back shortly.') }}
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button
                        type="button"
                        :disabled="isRetrying"
                        class="px-6 py-2.5 rounded-full text-sm font-medium text-white transition-all shadow-sm active:scale-95 disabled:opacity-60"
                        style="background: var(--color-primary, #1f4b5a)"
                        @click="retry"
                    >
                        {{ isRetrying ? t('weather.loading', undefined, 'Loading…') : t('weather.retry', undefined, 'Try again') }}
                    </button>
                    <Link
                        href="/"
                        class="px-6 py-2.5 rounded-full border border-slate-300 text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-all active:scale-95"
                    >
                        {{ t('weather.back_home', undefined, 'Return Home') }}
                    </Link>
                </div>
            </div>

            <!-- Successful Weather Display -->
            <div
                v-else
                class="space-y-8 lg:space-y-12 transition-opacity duration-200"
                :class="{ 'opacity-60 pointer-events-none': isSwitchingBranch }"
            >
                <!-- Featured / Most Recent Day Banner -->
                <div
                    v-if="latestDay"
                    class="relative overflow-hidden rounded-3xl p-6 lg:p-10 shadow-lg text-white"
                    style="background: linear-gradient(135deg, var(--color-primary, #0f2d3a) 0%, #1a4454 100%)"
                >
                    <!-- Background ambient graphic -->
                    <div
                        class="pointer-events-none absolute -end-16 -top-16 h-72 w-72 rounded-full opacity-15 blur-2xl"
                        style="background: var(--luxury-champagne, #c5a880)"
                        aria-hidden="true"
                    />

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <!-- Main Day Information -->
                        <div class="lg:col-span-7">
                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                <span
                                    class="px-3 py-1 rounded-full text-xs uppercase tracking-wider font-semibold"
                                    style="background: rgba(255, 255, 255, 0.18); color: #ffffff"
                                >
                                    {{ t('weather.latest_observation', undefined, 'Most Recent Day') }}
                                </span>
                                <span
                                    v-if="weather.location?.branch_name"
                                    class="px-2.5 py-0.5 rounded-full text-xs text-white/90 bg-white/10"
                                >
                                    {{ weather.location.branch_name }}
                                </span>
                                <span class="text-sm text-white/70">{{ formatLocalizedDate(latestDay.date) }}</span>
                            </div>

                            <div class="flex flex-wrap items-baseline gap-4 mt-2">
                                <span class="text-6xl lg:text-8xl font-light tracking-tighter tabular-nums" style="font-family: var(--font-display)">
                                    {{ Math.round(latestDay.temp_max) }}°
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-2xl lg:text-3xl font-medium text-white/95">
                                        {{ getConditionLabel(latestDay.condition_key) }}
                                    </span>
                                    <span class="text-sm text-white/75 mt-0.5">
                                        {{ t('weather.high') }}: {{ latestDay.temp_max }}°{{ weather.units.temperature }} · {{ t('weather.low') }}: {{ latestDay.temp_min }}°{{ weather.units.temperature }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Metrics in Featured Card -->
                        <div class="lg:col-span-5 grid grid-cols-2 gap-3 sm:gap-4 border-t lg:border-t-0 lg:border-s border-white/15 pt-6 lg:pt-0 lg:ps-8">
                            <div class="rounded-2xl p-4 bg-white/10 backdrop-blur-md">
                                <span class="block text-xs uppercase tracking-wider text-white/60 mb-1">{{ t('weather.feels_like') }}</span>
                                <span class="text-xl lg:text-2xl font-semibold tabular-nums">{{ latestDay.apparent_temp_max }}°{{ weather.units.temperature }}</span>
                            </div>
                            <div class="rounded-2xl p-4 bg-white/10 backdrop-blur-md">
                                <span class="block text-xs uppercase tracking-wider text-white/60 mb-1">{{ t('weather.high_low') }}</span>
                                <span class="text-xl lg:text-2xl font-semibold tabular-nums">{{ latestDay.temp_max }}° / {{ latestDay.temp_min }}°</span>
                            </div>
                            <div class="rounded-2xl p-4 bg-white/10 backdrop-blur-md">
                                <span class="block text-xs uppercase tracking-wider text-white/60 mb-1">{{ t('weather.precipitation') }}</span>
                                <span class="text-xl lg:text-2xl font-semibold tabular-nums">{{ latestDay.precipitation }} {{ weather.units.precipitation }}</span>
                            </div>
                            <div class="rounded-2xl p-4 bg-white/10 backdrop-blur-md">
                                <span class="block text-xs uppercase tracking-wider text-white/60 mb-1">{{ t('weather.wind') }}</span>
                                <span class="text-xl lg:text-2xl font-semibold tabular-nums">{{ latestDay.wind_speed }} {{ weather.units.wind }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5-Day Historical Cards Section -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl lg:text-2xl font-semibold text-slate-800" style="font-family: var(--font-display)">
                                {{ t('weather.daily_history', undefined, 'Daily History') }}
                            </h2>
                            <p class="text-xs lg:text-sm text-slate-400 mt-1">
                                {{ t('weather.historical_notice', undefined, 'Historical daily observations recorded over the last 5 days') }}
                            </p>
                        </div>
                    </div>

                    <!-- 5-Day Grid: Responsive across mobile, tablet, and desktop -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-5">
                        <div
                            v-for="day in weather.days"
                            :key="day.date"
                            class="group relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 lg:p-6 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5"
                            :class="{
                                'ring-2 ring-emerald-500/30 border-emerald-400/60 bg-emerald-50/20': day.date === latestDay?.date,
                            }"
                        >
                            <!-- Card Header: Day and Date -->
                            <div class="flex items-start justify-between gap-2 mb-4">
                                <div>
                                    <p class="text-lg font-semibold text-slate-800 capitalize leading-snug">
                                        {{ formatDayName(day.date) }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ formatShortDate(day.date) }}
                                    </p>
                                </div>
                                <span
                                    v-if="day.date === latestDay?.date"
                                    class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 tracking-wider"
                                >
                                    {{ t('weather.latest_observation', undefined, 'Recent') }}
                                </span>
                            </div>

                            <!-- Weather Condition Icon & Title -->
                            <div class="my-3 text-center">
                                <!-- Weather Icon Symbol -->
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-50 text-3xl group-hover:scale-110 transition-transform duration-200">
                                    <span v-if="day.icon === 'sun'">☀️</span>
                                    <span v-else-if="day.icon === 'sun-cloud' || day.icon === 'cloud-sun'">🌤️</span>
                                    <span v-else-if="day.icon === 'cloud'">☁️</span>
                                    <span v-else-if="day.icon === 'fog'">🌫️</span>
                                    <span v-else-if="day.icon === 'drizzle'">🌦️</span>
                                    <span v-else-if="day.icon === 'rain' || day.icon === 'showers'">🌧️</span>
                                    <span v-else-if="day.icon === 'snow' || day.icon === 'snow-showers'">❄️</span>
                                    <span v-else-if="day.icon === 'thunderstorm'">⛈️</span>
                                    <span v-else>🌤️</span>
                                </div>

                                <p class="mt-2 text-sm font-medium text-slate-700 min-h-[2.5rem] flex items-center justify-center leading-snug">
                                    {{ getConditionLabel(day.condition_key) }}
                                </p>
                            </div>

                            <!-- Temperatures (High / Low) -->
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <div class="flex items-baseline justify-between">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-2xl font-semibold text-slate-900 tabular-nums">
                                            {{ Math.round(day.temp_max) }}°
                                        </span>
                                        <span class="text-xs text-slate-400 font-normal">
                                            {{ t('weather.high') }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline gap-1 text-slate-500">
                                        <span class="text-lg tabular-nums">
                                            {{ Math.round(day.temp_min) }}°
                                        </span>
                                        <span class="text-xs text-slate-400 font-normal">
                                            {{ t('weather.low') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Subtle wind & rain metrics -->
                                <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400">
                                    <span class="flex items-center gap-1">
                                        💧 {{ day.precipitation }} {{ weather.units.precipitation }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        💨 {{ Math.round(day.wind_speed) }} {{ weather.units.wind }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.weather-page {
    font-family: var(--font-sans);
}
</style>
