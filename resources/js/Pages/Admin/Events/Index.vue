<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import type { HotelEvent } from '@/types/content';
import type { Paginated } from '@/types/facility';

defineOptions({ layout: AdminLayout });

defineProps<{ events: Paginated<HotelEvent> }>();

const destroy = (event: HotelEvent) => {
  if (confirm(`Delete "${event.title}"?`)) router.delete(`/admin/events/${event.id}`);
};
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold text-slate-800">Events</h1>
      <Link href="/admin/events/create" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-900">+ Add Event</Link>
    </div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-400">
          <tr><th class="px-4 py-2">Title</th><th class="px-4 py-2">Date</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="event in events.data" :key="event.id">
            <td class="px-4 py-2 font-medium text-slate-700">{{ event.title }}</td>
            <td class="px-4 py-2 text-slate-500">{{ event.start_date }}</td>
            <td class="px-4 py-2">
              <span class="rounded-full px-2 py-0.5 text-xs" :class="event.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">{{ event.status }}</span>
            </td>
            <td class="px-4 py-2 text-right space-x-3">
              <Link :href="`/admin/events/${event.id}/edit`" class="text-slate-500 hover:text-slate-800">Edit</Link>
              <button class="text-red-500 hover:text-red-700" @click="destroy(event)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
