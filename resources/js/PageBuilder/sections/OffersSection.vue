<script setup lang="ts">
import OfferCard from '@/Components/Cards/OfferCard.vue';
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';
import type { Offer } from '@/types/content';

interface OffersProps {
  title?: string;
  limit?: number;
  active_only?: boolean;
  featured_only?: boolean;
}
interface OffersData {
  offers: Offer[];
}

defineProps<{
  props: OffersProps;
  settings: SectionSettings;
  data?: OffersData;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-3">{{ props.title }}</h2>

    <div class="grid grid-cols-2 gap-3">
      <OfferCard v-for="offer in data?.offers ?? []" :key="offer.id" :offer="offer" />
    </div>

    <p v-if="mode === 'edit' && !data?.offers?.length" class="text-xs text-slate-400 mt-2">
      No offers match these filters yet.
    </p>
  </section>
</template>
