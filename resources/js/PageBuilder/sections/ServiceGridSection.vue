<script setup lang="ts">
import ServiceCard from '@/Components/Cards/ServiceCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import type { Service } from '@/types/content';

interface ServiceGridProps {
  title?: string;
  limit?: number;
}
interface ServiceGridData {
  services: Service[];
}

defineProps<{
  props: ServiceGridProps;
  settings: SectionSettings;
  data?: ServiceGridData;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-2">{{ props.title }}</h2>

    <div>
      <ServiceCard v-for="service in data?.services ?? []" :key="service.id" :service="service" />
    </div>

    <p v-if="mode === 'edit' && !data?.services?.length" class="text-xs text-slate-400 mt-2">
      No services published yet.
    </p>
  </section>
</template>
