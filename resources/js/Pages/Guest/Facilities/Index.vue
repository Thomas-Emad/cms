<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FacilityCard from '@/Components/Cards/FacilityCard.vue';
import type { Facility } from '@/types/facility';

defineOptions({ layout: GuestLayout });

withDefaults(
    defineProps<{
        facilities: Facility[];
        category: string | null;
        title?: string;
    }>(),
    { title: 'Facilities' },
);
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 lg:px-10 pt-24 lg:pt-28 pb-16 lg:pb-24">
        <h1 class="reveal text-3xl lg:text-4xl mb-8 lg:mb-10" style="font-family: var(--font-display); color: var(--color-primary, #1f4b5a)" v-reveal>
            {{ title }}
        </h1>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            <div v-for="(facility, i) in facilities" :key="facility.id" class="reveal" v-reveal="{ delay: i * 70 }">
                <FacilityCard :facility="facility" />
            </div>
        </div>

        <p v-if="!facilities.length" class="text-sm text-slate-400 mt-10 text-center">Nothing available yet.</p>
    </div>
</template>
