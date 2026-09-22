<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Hotel } from '@/types/hotel';
import ClassicShell from './guest/ClassicShell.vue';
import TvShell from './guest/TvShell.vue';
import { resolveConfig } from './guest/shellConfig';

/**
 * Hospitality-screen (touch, landscape) layout switcher.
 *
 * The admin picks a template under Website > Guest Layout; the server shares it as the
 * `guestLayout` prop. To add a template: create a shell component in ./guest/, register it in
 * shellConfig.ts (TEMPLATES) and here, and allow its id in App\Services\Layout\GuestLayoutConfig.
 */
defineProps<{ hotel?: Hotel }>();

const page = usePage();
const config = computed(() => resolveConfig((page.props as { guestLayout?: unknown }).guestLayout));
</script>

<template>
    <TvShell v-if="config.template === 'tv'" :hotel="hotel" :config="config"><slot /></TvShell>
    <ClassicShell v-else :hotel="hotel" :config="config"><slot /></ClassicShell>
</template>
