<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
import type { Hotel } from '@/types/hotel';
import ClassicShell from './guest/ClassicShell.vue';
import TvShell from './guest/TvShell.vue';
import { resolveConfig } from './guest/shellConfig';
import { useTheme, type ThemeInput } from '@/composables/useTheme';

/**
 * Hospitality-screen (touch, landscape) layout switcher.
 *
 * The admin picks a template under Website > Guest Layout; the server shares it as the
 * `guestLayout` prop and the active theme as `theme`.
 */
defineProps<{ hotel?: Hotel }>();

const page = usePage();
const config = computed(() => resolveConfig((page.props as { guestLayout?: unknown }).guestLayout));
const theme = computed(() => (page.props as { theme?: ThemeInput }).theme);

const { applyTheme } = useTheme();

onMounted(() => {
    applyTheme(theme.value);
});

watch(theme, (newTheme) => {
    applyTheme(newTheme);
}, { deep: true });
</script>

<template>
    <TvShell v-if="config.template === 'tv'" :hotel="hotel" :config="config"><slot /></TvShell>
    <ClassicShell v-else :hotel="hotel" :config="config"><slot /></ClassicShell>
</template>
