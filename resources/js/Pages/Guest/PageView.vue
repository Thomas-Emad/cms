<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SectionRenderer from '@/PageBuilder/SectionRenderer.vue';
import type { Section } from '@/types/pageBuilder';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
  page: { id: number; name: string; slug: string; seo_title?: string | null; seo_description?: string | null };
  sections: Section[];
}>();
</script>

<template>
  <Head :title="props.page.seo_title ?? props.page.name">
    <meta v-if="props.page.seo_description" name="description" :content="props.page.seo_description" />
  </Head>

  <div>
    <SectionRenderer v-for="section in props.sections" :key="section.id" :section="section" mode="live" />
  </div>
</template>
