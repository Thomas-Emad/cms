<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GuestShell from '@/Layouts/GuestShell.vue';
import SectionRenderer from '@/PageBuilder/SectionRenderer.vue';
import type { Section } from '@/types/pageBuilder';
import { computed } from 'vue';

defineOptions({ layout: GuestShell });

const props = defineProps<{
  page: { id: number; name: string; slug: string; layout?: 'scroll' | 'fullscreen'; seo_title?: string | null; seo_description?: string | null };
  sections: Section[];
}>();

// Sections that are full-bleed and meant to start behind the fixed top bar.
// Anything else needs top clearance, or its first line hides under the bar.
const FULL_BLEED_FIRST = ['hero', 'restaurant-hero', 'story-slideshow', 'app-launcher'];
const needsTopClearance = computed(() => !FULL_BLEED_FIRST.includes(props.sections[0]?.type ?? ''));

// 'fullscreen' pages (e.g. the Smart-TV-style home screen) never scroll:
// the whole page is locked to the viewport height and each section snaps
// to fill it edge-to-edge. 'scroll' (the default) is the existing
// behavior - content flows and the page scrolls normally. This is a
// page-level setting (Admin > Pages > Builder > Layout), not per-section.
const isFullscreen = computed(() => props.page.layout === 'fullscreen');
</script>

<template>
  <Head :title="props.page.seo_title ?? props.page.name">
    <meta v-if="props.page.seo_description" name="description" :content="props.page.seo_description" />
  </Head>

  <div
    :class="isFullscreen ? 'h-screen w-screen overflow-hidden snap-y snap-mandatory overscroll-none' : undefined"
    :style="needsTopClearance ? { paddingTop: 'var(--kiosk-topbar-h, 0px)' } : undefined"
  >
    <div
      v-for="section in props.sections"
      :key="section.id"
      :class="isFullscreen ? 'h-screen w-screen snap-start overflow-hidden' : undefined"
    >
      <SectionRenderer :section="section" mode="live" />
    </div>
  </div>
</template>
