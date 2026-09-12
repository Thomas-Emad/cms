<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import type { Facility, Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{
  facilities: Paginated<Facility>;
}>();

const destroy = (facility: Facility) => {
  if (confirm(`Delete "${facility.name}"? This cannot be undone.`)) {
    router.delete(`/admin/facilities/${facility.id}`);
  }
};
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">Facilities</h1>
      <Link
        href="/admin/facilities/create"
        class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900"
      >
        + Add Facility
      </Link>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
          <tr>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Category</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="facility in facilities.data" :key="facility.id">
            <td class="px-4 py-2 font-medium text-slate-700">{{ facility.name }}</td>
            <td class="px-4 py-2 text-slate-500">{{ facility.category }}</td>
            <td class="px-4 py-2">
              <span
                class="rounded-full px-2 py-0.5 text-xs"
                :class="facility.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
              >
                {{ facility.status }}
              </span>
            </td>
            <td class="px-4 py-2 text-right space-x-3">
              <Link :href="`/admin/facilities/${facility.id}/edit`" class="text-slate-500 hover:text-slate-800">
                Edit
              </Link>
              <button class="text-red-500 hover:text-red-700" @click="destroy(facility)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="!facilities.data.length" class="text-sm text-slate-400 mt-6 text-center">
      No facilities yet — add your first one.
    </p>
  </div>
</template>
