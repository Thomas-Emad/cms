<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaManager from '@/Components/Admin/MediaManager.vue';
import type { MediaItem } from '@/types/room';

defineOptions({ layout: AdminLayout });

defineProps<{ hotel_id: number; items: MediaItem[] }>();
</script>

<template>
    <div class="max-w-4xl">
        <h1 class="text-xl font-semibold text-slate-800">{{ $t('admin.gallery.title', 'Gallery') }}</h1>
        <p class="mt-1 mb-4 text-sm text-slate-500">{{ $t('admin.gallery.subtitle') }}</p>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <MediaManager mediable-type="hotel" :mediable-id="hotel_id" collection="gallery" :items="items"
                :label="$t('admin.gallery.hotel_photos', 'Hotel photos')"
                :hint="$t('admin.gallery.reorder_hint', 'Use the arrows to reorder.')" />
        </div>
    </div>
</template>
