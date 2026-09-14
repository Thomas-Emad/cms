<script setup lang="ts">
import type { RenderMode, SectionSettings } from '@/types/pageBuilder';

interface MenuItem { name: string; description?: string | null; price: string; dietary_info?: string[] | null; }
interface MenuCategory { name: string; items: MenuItem[]; }
interface Data { menu: { categories: MenuCategory[] } | null; }

defineProps<{
  props: { title?: string };
  settings: SectionSettings;
  data?: Data;
  mode: RenderMode;
}>();
</script>

<template>
  <section class="mx-auto max-w-screen-sm px-4 py-6">
    <h2 v-if="props.title" class="text-lg font-semibold text-slate-800 mb-4">{{ props.title }}</h2>

    <div v-if="data?.menu" class="space-y-6">
      <div v-for="category in data.menu.categories" :key="category.name">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400 mb-2">{{ category.name }}</h3>
        <ul class="divide-y divide-slate-100">
          <li v-for="item in category.items" :key="item.name" class="py-2 flex justify-between gap-4">
            <div>
              <p class="font-medium text-slate-700">{{ item.name }}</p>
              <p v-if="item.description" class="text-sm text-slate-500">{{ item.description }}</p>
            </div>
            <span class="shrink-0 text-slate-700">${{ item.price }}</span>
          </li>
        </ul>
      </div>
    </div>
    <p v-else class="text-xs text-slate-400">No active menu for this restaurant yet.</p>
  </section>
</template>
