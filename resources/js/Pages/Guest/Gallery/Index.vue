<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GalleryGrid from '@/Components/GalleryGrid.vue';
import type { MediaItem } from '@/types/room';

defineOptions({ layout: GuestLayout });

defineProps<{ images: MediaItem[] }>();
</script>

<template>
    <div class="mx-auto max-w-7xl px-10 pt-28 pb-16">
        <h1 class="reveal text-5xl mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>Gallery</h1>

        <GalleryGrid v-if="images.length" :images="images" />
        <p v-else class="text-xl text-slate-400 mt-16 text-center">Photos will appear here soon.</p>
    </div>
</template>
