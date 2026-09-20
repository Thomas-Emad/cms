<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Room } from '@/types/room';

const props = defineProps<{
    room: Pick<Room, 'name' | 'slug' | 'short_description' | 'size_sqm' | 'max_guests' | 'cover_image_url'>;
}>();

const facts = computed(() =>
    [
        props.room.size_sqm ? `${props.room.size_sqm} m²` : null,
        props.room.max_guests ? `${props.room.max_guests} guest${props.room.max_guests > 1 ? 's' : ''}` : null,
    ]
        .filter(Boolean)
        .join(' · '),
);
</script>

<template>
    <Link :href="`/rooms/${room.slug}`" class="group relative block aspect-[4/3] overflow-hidden active:scale-[0.99] transition-transform duration-150">
        <img v-if="room.cover_image_url" :src="room.cover_image_url" :alt="room.name" draggable="false" class="absolute inset-0 h-full w-full object-cover" />
        <div v-else class="absolute inset-0 bg-slate-200" />
        <div class="absolute inset-0" style="background: var(--atmosphere-gradient)" />
        <div class="absolute inset-x-0 bottom-0 p-6">
            <p v-if="facts" class="text-sm uppercase tracking-wide text-white/75">{{ facts }}</p>
            <h3 class="mt-1 text-3xl text-white" style="font-family: var(--font-display)">{{ room.name }}</h3>
            <p v-if="room.short_description" class="mt-1 text-base text-white/80 line-clamp-2">{{ room.short_description }}</p>
        </div>
    </Link>
</template>
