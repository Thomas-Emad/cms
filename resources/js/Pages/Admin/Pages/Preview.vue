<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SectionRenderer from '@/PageBuilder/SectionRenderer.vue';
import type { Section } from '@/types/pageBuilder';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
  page: { id: number; name: string; slug: string };
  sections: Section[];
  backToBuilderHref: string;
}>();
</script>

<template>
  <Head :title="`Preview — ${props.page.name}`" />

  <div class="sticky top-14 z-30 bg-amber-50 border-b border-amber-200 px-4 py-2 text-xs text-amber-700 flex items-center justify-between">
    <span>Preview mode — showing the current draft, not the published page.</span>
    <Link :href="props.backToBuilderHref" class="underline">Back to Builder</Link>
  </div>

  <!--
    Identical to Guest/PageView.vue's rendering loop, just mode="preview"
    instead of "live" and fed draft-resolved sections instead of
    published-resolved ones. No second rendering system.
  -->
  <div>
    <SectionRenderer v-for="section in props.sections" :key="section.id" :section="section" mode="preview" />
  </div>
</template>
