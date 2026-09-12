<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Hotel } from '@/types/hotel';

defineOptions({ layout: AdminLayout });

defineProps<{
  hotel: Hotel;
}>();
</script>

<template>
  <div>
    <h1 class="text-xl font-semibold text-slate-800">Dashboard</h1>
    <p class="mt-1 text-sm text-slate-500">
      Managing <strong>{{ hotel.name }}</strong> ({{ hotel.status }})
    </p>

    <div class="mt-6 rounded-lg border border-dashed border-slate-300 p-6 text-sm text-slate-400">
      Content widgets (facility count, upcoming events, draft pages...) land in Phase 2+.
    </div>
  </div>
</template>
