<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestShell from '@/Layouts/GuestShell.vue';
import MapExperience from '@/Map/MapExperience.vue';
import type { HotelMapData } from '@/Map/types';

defineOptions({ layout: GuestShell });

const props = defineProps<{ map: HotelMapData | null; place?: string | null }>();

// Guard against an empty or half-imported map so the guest sees a message, not a broken screen.
const usable = computed(() => !!props.map && props.map.floors?.length > 0 && props.map.nodes?.length > 0 && props.map.locations?.length > 0);
</script>

<template>

    <Head :title="$t('map.title')" />
    <MapExperience v-if="usable" :data="map!" :place="place" />
    <div v-else class="mx-auto flex max-w-3xl flex-col items-center px-10 pt-40 pb-16 text-center"
        data-testid="map-empty">
        <p class="text-7xl" aria-hidden="true">🗺️</p>
        <h1 class="mt-6 text-4xl" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)">{{
            $t('map.empty_title') }}</h1>
        <p class="mt-3 text-xl text-slate-500">{{ $t('map.empty_desc') }}</p>
    </div>
</template>
