<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Facility } from '@/types/facility';

defineProps<{
  facility: Pick<Facility, 'name' | 'slug' | 'short_description' | 'category' | 'cover_image'>;
}>();
</script>

<template>
  <Link
    :href="`/facilities/${facility.slug}`"
    class="block overflow-hidden bg-white shadow-sm"
    style="border-radius: var(--radius, 8px)"
  >
    <div class="aspect-[4/3] bg-slate-100">
      <img
        v-if="facility.cover_image_url"
        :src="facility.cover_image_url"
        :alt="facility.name"
        class="h-full w-full object-cover"
      />
    </div>
    <div class="p-3">
      <p class="text-xs uppercase tracking-wide text-slate-400">{{ facility.category }}</p>
      <h3 class="font-medium text-slate-800">{{ facility.name }}</h3>
      <p v-if="facility.short_description" class="mt-0.5 text-sm text-slate-500 line-clamp-2">
        {{ facility.short_description }}
      </p>
    </div>
  </Link>
</template>
