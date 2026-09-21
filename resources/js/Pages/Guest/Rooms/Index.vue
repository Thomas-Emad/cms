<script setup lang="ts">
import { computed } from 'vue';
import GuestShell from '@/Layouts/GuestShell.vue';
import Showcase from '@/Components/Showcase.vue';
import type { Room } from '@/types/room';

defineOptions({ layout: GuestShell });

const props = defineProps<{ rooms: Room[] }>();

const items = computed(() =>
    props.rooms.map((r) => ({
        id: r.id,
        title: r.name,
        subtitle: r.short_description,
        facts: [r.size_sqm ? `${r.size_sqm} m²` : null, r.max_guests ? `${r.max_guests} guest${r.max_guests > 1 ? 's' : ''}` : null].filter(Boolean).join(' · '),
        image: r.cover_image_url,
        href: `/rooms/${r.slug}`,
    })),
);
</script>

<template>
    <Showcase :items="items" heading="Rooms & Suites" empty-text="Rooms will appear here soon." action-label="View room" />
</template>
