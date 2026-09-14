<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SectionRenderer from '@/PageBuilder/SectionRenderer.vue';
import type { Section } from '@/types/pageBuilder';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
  restaurant: { id: number; name: string; slug: string };
  sections: Section[];
}>();
</script>

<template>
  <Head :title="props.restaurant.name" />

  <!--
    Identical loop to Guest/PageView.vue - the layout is entirely
    determined by whatever sections the Restaurant Builder produced, not
    hardcoded here. mode="live" because this only ever renders a
    PUBLISHED presentation (see RestaurantController::show()).
  -->
  <div>
    <SectionRenderer v-for="section in props.sections" :key="section.id" :section="section" mode="live" />
  </div>
</template>
