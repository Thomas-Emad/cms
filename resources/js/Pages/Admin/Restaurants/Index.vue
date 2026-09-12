<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import type { Restaurant } from '@/types/restaurant';
import type { Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{
  restaurants: Paginated<Restaurant>;
}>();

const destroy = (restaurant: Restaurant) => {
  if (confirm(`Delete "${restaurant.name}"? This cannot be undone.`)) {
    router.delete(`/admin/restaurants/${restaurant.id}`);
  }
};
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">Restaurants</h1>
      <Link href="/admin/restaurants/create" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900">
        + Add Restaurant
      </Link>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
          <tr>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Cuisine</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="restaurant in restaurants.data" :key="restaurant.id">
            <td class="px-4 py-2 font-medium text-slate-700">{{ restaurant.name }}</td>
            <td class="px-4 py-2 text-slate-500">{{ restaurant.cuisine }}</td>
            <td class="px-4 py-2">
              <span
                class="rounded-full px-2 py-0.5 text-xs"
                :class="restaurant.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
              >
                {{ restaurant.status }}
              </span>
            </td>
            <td class="px-4 py-2 text-right space-x-3">
              <Link :href="`/admin/restaurants/${restaurant.id}/edit`" class="text-slate-500 hover:text-slate-800">Edit</Link>
              <button class="text-red-500 hover:text-red-700" @click="destroy(restaurant)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
