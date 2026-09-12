<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FacilityCard from '@/Components/Cards/FacilityCard.vue';
import type { Facility } from '@/types/facility';

defineOptions({ layout: GuestLayout });

defineProps<{
  facilities: Facility[];
  category: string | null;
}>();
</script>

<template>
  <div class="mx-auto max-w-screen-sm px-4 py-6">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Facilities</h1>

    <div class="grid grid-cols-2 gap-3">
      <FacilityCard v-for="facility in facilities" :key="facility.id" :facility="facility" />
    </div>

    <p v-if="!facilities.length" class="text-sm text-slate-400 mt-6 text-center">
      No facilities available yet.
    </p>
  </div>
</template>
