<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GuestLayout from './GuestLayout.vue';
import TvGuestLayout from './TvGuestLayout.vue';
import type { Hotel } from '@/types/hotel';

defineProps<{
    hotel?: Hotel;
}>();

/**
 * The layout every guest page actually declares (`defineOptions({ layout:
 * GuestShell })`) instead of GuestLayout directly. It picks the real shell
 * per-hotel based on Admin > Settings > Guest View
 * (HotelSettings.guest_view, shared as page.props.guestView - see
 * HandleInertiaRequests):
 *
 *   'classic' (default) -> GuestLayout   (dark top bar + bottom dock nav)
 *   'tv'                 -> TvGuestLayout (chrome-less Smart-TV-style shell)
 *
 * Centralizing the choice here means every guest page gets the switch for
 * free and never needs to know which shell is active.
 */
const guestView = computed(() => (usePage().props.guestView as string | undefined) ?? 'classic');
const ActiveLayout = computed(() => (guestView.value === 'tv' ? TvGuestLayout : GuestLayout));
</script>

<template>
    <component :is="ActiveLayout" :hotel="hotel">
        <slot />
    </component>
</template>
