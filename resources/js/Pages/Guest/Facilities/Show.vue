<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { Facility } from '@/types/facility';

defineOptions({ layout: GuestLayout });

defineProps<{
  facility: Facility;
}>();
</script>

<template>
  <div>
    <div class="aspect-[16/9] bg-slate-100">
      <img
        v-if="facility.cover_image_url"
        :src="facility.cover_image_url"
        :alt="facility.name"
        class="h-full w-full object-cover"
      />
    </div>

    <div class="mx-auto max-w-screen-sm px-4 py-6">
      <p class="text-xs uppercase tracking-wide text-slate-400">{{ facility.category }}</p>
      <h1 class="text-xl font-semibold text-slate-800">{{ facility.name }}</h1>

      <p v-if="facility.description" class="mt-3 text-sm text-slate-600 whitespace-pre-line">
        {{ facility.description }}
      </p>

      <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
        <div v-if="facility.building">
          <p class="text-slate-400">Location</p>
          <p class="text-slate-700">
            {{ [facility.building, facility.floor, facility.wing].filter(Boolean).join(', ') }}
          </p>
        </div>
        <div v-if="facility.phone">
          <p class="text-slate-400">Phone</p>
          <p class="text-slate-700">{{ facility.phone }}</p>
        </div>
      </div>

      <ul v-if="facility.amenities?.length" class="mt-6 flex flex-wrap gap-2">
        <li
          v-for="amenity in facility.amenities"
          :key="amenity"
          class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600"
        >
          {{ amenity }}
        </li>
      </ul>
    </div>
  </div>
</template>
