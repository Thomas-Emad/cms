<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import GuestShell from '@/Layouts/GuestShell.vue';
import StoryPlayer from '@/Components/StoryPlayer.vue';
import { useI18n } from '@/i18n';
import type { MediaItem, Room } from '@/types/room';

defineOptions({ layout: GuestShell });

const props = defineProps<{ room: Room & { slides: MediaItem[] } }>();

const { t } = useI18n();

const factLine = computed(() =>
    [
        props.room.size_sqm ? `${props.room.size_sqm} ${t('rooms.sqm')}` : null,
        props.room.max_guests
            ? `${props.room.max_guests} ${props.room.max_guests > 1 ? t('rooms.guests') : t('rooms.guest_single')}`
            : null,
        props.room.bed_type,
        props.room.view ? `${props.room.view} ${t('rooms.view')}` : null,
    ]
        .filter(Boolean)
        .join(' · '),
);
</script>

<template>
    <div>
        <!-- The room plays as a story: main photo, then gallery photos and videos. -->
        <div class="relative">
            <StoryPlayer :slides="room.slides" :interval-seconds="6" height="calc(100dvh - var(--kiosk-dock-h, 0px))">
                <p v-if="factLine" class="text-white/80 text-xl uppercase tracking-wider mb-3">{{ factLine }}</p>
                <h1 class="text-white text-7xl leading-[1.05] max-w-4xl" style="font-family: var(--font-display)">{{ room.name }}</h1>
                <p v-if="room.short_description" class="mt-4 text-white/85 text-2xl max-w-3xl">{{ room.short_description }}</p>
                <p class="mt-8 text-white/60 text-base uppercase tracking-[0.25em]">{{ $t('common.swipe_up_details') }}</p>
            </StoryPlayer>

            <Link
                href="/rooms"
                class="absolute start-10 z-30 flex items-center gap-2 h-12 px-6 rounded-full bg-black/40 text-white text-base uppercase tracking-wider active:scale-95 transition-transform backdrop-blur-sm"
                style="top: calc(var(--kiosk-topbar-h, 0px) + 2.25rem)"
            >
                <span class="rtl:rotate-180 inline-block">←</span> {{ $t('rooms.all_rooms') }}
            </Link>
        </div>

        <div class="mx-auto max-w-7xl px-10 py-16">
            <p v-if="room.description" class="text-2xl leading-relaxed text-slate-700 max-w-4xl whitespace-pre-line" style="font-family: var(--font-display)">
                {{ room.description }}
            </p>

            <ul v-if="room.features?.length" class="mt-10 flex flex-wrap gap-3">
                <li v-for="feature in room.features" :key="feature" class="rounded-full border border-slate-200 px-5 py-2 text-base uppercase tracking-wide text-slate-600">
                    {{ feature }}
                </li>
            </ul>

            <p v-if="!room.description && !room.features?.length" class="text-xl text-slate-400">{{ $t('common.details_coming_soon') }}</p>
        </div>
    </div>
</template>
