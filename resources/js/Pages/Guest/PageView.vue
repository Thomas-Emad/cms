<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SectionRenderer from '@/PageBuilder/SectionRenderer.vue';
import type { Section } from '@/types/pageBuilder';
import { computed } from 'vue';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
  page: { id: number; name: string; slug: string; seo_title?: string | null; seo_description?: string | null };
  sections: Section[];
}>();

// Sections that are full-bleed and meant to start behind the fixed top bar.
// Anything else needs top clearance, or its first line hides under the bar.
const FULL_BLEED_FIRST = ['hero', 'restaurant-hero', 'story-slideshow'];
const needsTopClearance = computed(() => !FULL_BLEED_FIRST.includes(props.sections[0]?.type ?? ''));
</script>

<template>
  <Head :title="props.page.seo_title ?? props.page.name">
    <meta v-if="props.page.seo_description" name="description" :content="props.page.seo_description" />
  </Head>

  <div :style="needsTopClearance ? { paddingTop: 'var(--kiosk-topbar-h, 0px)' } : undefined">
    <SectionRenderer v-for="section in props.sections" :key="section.id" :section="section" mode="live" />
  </div>
</template>
