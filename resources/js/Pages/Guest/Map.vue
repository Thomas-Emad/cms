<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import MapExperience from '@/Map/MapExperience.vue';
import type { HotelMapData } from '@/Map/types';

defineOptions({ layout: GuestLayout });

const props = defineProps<{ map: HotelMapData | null }>();

// Guard against an empty or half-imported map so the guest sees a message, not a broken screen.
const usable = computed(() => !!props.map && props.map.floors?.length > 0 && props.map.nodes?.length > 0 && props.map.locations?.length > 0);
</script>

<template>
    <Head title="Hotel Map" />
    <MapExperience v-if="usable" :data="map!" />
    <div v-else class="mx-auto flex max-w-3xl flex-col items-center px-10 pt-40 pb-16 text-center" data-testid="map-empty">
        <p class="text-7xl" aria-hidden="true">🗺️</p>
        <h1 class="mt-6 text-4xl" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">The hotel map isn't ready yet</h1>
        <p class="mt-3 text-xl text-slate-500">Please ask at reception for directions. We're sorry for the inconvenience.</p>
    </div>
</template>
