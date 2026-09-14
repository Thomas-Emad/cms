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
    <!--
    Full-bleed by default - the brief specifically calls out "avoid
    putting every image inside a small card". Caption reads as a small
    metadata line under the image, left-aligned within the page's
    content width rather than centered under a boxed photo.
  -->
    <figure
        class="reveal-scale is-visible w-full"
        :class="{
            'py-8': settings.padding === 'large',
            'py-4': settings.padding === 'medium' || !settings.padding,
            'py-2': settings.padding === 'small',
            'py-0': settings.padding === 'none',
        }"
    >
        <a v-if="props.link_url && data?.media" :href="props.link_url" class="block">
            <img :src="data.media.url" :alt="data.media.alt_text ?? props.caption ?? ''" class="w-full max-h-[80vh] object-cover" />
        </a>
        <img
            v-else-if="data?.media"
            :src="data.media.url"
            :alt="data.media.alt_text ?? props.caption ?? ''"
            class="w-full max-h-[80vh] object-cover"
        />
        <div v-else class="mx-auto max-w-7xl px-6 lg:px-10 aspect-video bg-slate-100 flex items-center justify-center text-xs text-slate-400">
            No image selected
        </div>
        <figcaption v-if="props.caption" class="mx-auto max-w-7xl px-6 lg:px-10 mt-3 text-xs uppercase tracking-wide text-slate-400">
            {{ props.caption }}
        </figcaption>
    </figure>
</template>
