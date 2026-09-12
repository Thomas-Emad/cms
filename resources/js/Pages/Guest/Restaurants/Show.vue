<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { Restaurant } from '@/types/restaurant';

defineOptions({ layout: GuestLayout });

defineProps<{
  restaurant: Restaurant;
}>();
</script>

<template>
  <div>
    <div class="aspect-[16/9] bg-slate-100">
      <img
        v-if="restaurant.cover_image_url"
        :src="restaurant.cover_image_url"
        :alt="restaurant.name"
        class="h-full w-full object-cover"
      />
    </div>

    <div class="mx-auto max-w-screen-sm px-4 py-6">
      <p v-if="restaurant.cuisine" class="text-xs uppercase tracking-wide text-slate-400">
        {{ restaurant.cuisine }}
      </p>
      <h1 class="text-xl font-semibold text-slate-800">{{ restaurant.name }}</h1>
      <p v-if="restaurant.description" class="mt-3 text-sm text-slate-600">{{ restaurant.description }}</p>

      <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div v-if="restaurant.dress_code">
          <p class="text-slate-400">Dress code</p>
          <p class="text-slate-700">{{ restaurant.dress_code }}</p>
        </div>
        <div v-if="restaurant.reservation_url">
          <p class="text-slate-400">Reservations</p>
          <a :href="restaurant.reservation_url" class="text-slate-700 underline">Book a table</a>
        </div>
      </div>

      <div v-if="restaurant.active_menu" class="mt-8 space-y-6">
        <h2 class="text-lg font-semibold text-slate-800">Menu</h2>
        <div v-for="category in restaurant.active_menu.categories" :key="category.id">
          <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400 mb-2">
            {{ category.name }}
          </h3>
          <ul class="divide-y divide-slate-100">
            <li v-for="item in category.items" :key="item.id" class="py-2 flex justify-between gap-4">
              <div>
                <p class="font-medium text-slate-700">{{ item.name }}</p>
                <p v-if="item.description" class="text-sm text-slate-500">{{ item.description }}</p>
              </div>
              <span class="shrink-0 text-slate-700">${{ item.price }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
