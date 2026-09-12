<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ServiceCard from '@/Components/Cards/ServiceCard.vue';
import type { Service } from '@/types/content';

defineOptions({ layout: GuestLayout });

defineProps<{ services: Service[] }>();
</script>

<template>
  <div class="mx-auto max-w-screen-sm px-4 py-6">
    <h1 class="text-xl font-semibold text-slate-800 mb-2">Hotel Services</h1>
    <div>
      <ServiceCard v-for="service in services" :key="service.id" :service="service" />
    </div>
  </div>
</template>
