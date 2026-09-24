<script setup lang="ts">
import { computed } from 'vue';
import GuestShell from '@/Layouts/GuestShell.vue';
import Showcase from '@/Components/Showcase.vue';
import { useI18n } from '@/i18n';
import type { Room } from '@/types/room';

defineOptions({ layout: GuestShell });

const props = defineProps<{ rooms: Room[] }>();

const { t } = useI18n();

const items = computed(() =>
    props.rooms.map((r) => ({
        id: r.id,
        title: r.name,
        subtitle: r.short_description,
        facts: [
            r.size_sqm ? `${r.size_sqm} ${t('rooms.sqm')}` : null,
            r.max_guests ? `${r.max_guests} ${r.max_guests > 1 ? t('rooms.guests') : t('rooms.guest_single')}` : null,
        ].filter(Boolean).join(' · '),
        image: r.cover_image_url,
        href: `/rooms/${r.slug}`,
    })),
);
</script>

<template>
    <Showcase :items="items" :heading="$t('rooms.title')" :empty-text="$t('rooms.empty')" :action-label="$t('rooms.view_room')" />
</template>
