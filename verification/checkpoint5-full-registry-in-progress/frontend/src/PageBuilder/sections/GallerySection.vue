<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface GalleryProps {
  title?: string;
  media_ids?: number[];
}
interface GalleryData {
  media?: { url: string; alt_text: string | null }[];
}

defineProps<{
  props: GalleryProps;
  settings: SectionSettings;
  data?: GalleryData;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-3">{{ props.title }}</h2>

    <div v-if="data?.media?.length" class="grid grid-cols-3 gap-2">
      <img
        v-for="(item, i) in data.media"
        :key="i"
        :src="item.url"
        :alt="item.alt_text ?? ''"
        class="aspect-square w-full object-cover"
        style="border-radius: var(--radius, 8px)"
      />
    </div>
    <p v-else class="text-xs text-slate-400">No images selected yet.</p>
  </section>
</template>
