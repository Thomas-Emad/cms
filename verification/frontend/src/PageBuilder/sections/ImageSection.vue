<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface ImageProps {
  media_id?: number | null;
  caption?: string;
  link_url?: string;
}
interface ImageData {
  media?: { url: string; alt_text: string | null } | null;
}

defineProps<{
  props: ImageProps;
  settings: SectionSettings;
  data?: ImageData;
  mode: RenderMode;
}>();
</script>

<template>
  <figure class="mx-auto max-w-screen-sm px-4 py-4">
    <a v-if="props.link_url && data?.media" :href="props.link_url">
      <img :src="data.media.url" :alt="data.media.alt_text ?? props.caption ?? ''" class="w-full object-cover" style="border-radius: var(--radius, 8px)" />
    </a>
    <img v-else-if="data?.media" :src="data.media.url" :alt="data.media.alt_text ?? props.caption ?? ''" class="w-full object-cover" style="border-radius: var(--radius, 8px)" />
    <div v-else class="aspect-video bg-slate-100 flex items-center justify-center text-xs text-slate-400" style="border-radius: var(--radius, 8px)">
      No image selected
    </div>
    <figcaption v-if="props.caption" class="mt-2 text-xs text-slate-500 text-center">{{ props.caption }}</figcaption>
  </figure>
</template>
