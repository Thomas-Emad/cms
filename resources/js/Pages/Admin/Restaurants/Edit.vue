<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import type { Restaurant } from '@/types/restaurant';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  restaurant: Restaurant | null;
}>();

const isEdit = !!props.restaurant;

const form = useForm({
  name: props.restaurant?.name ?? '',
  slug: props.restaurant?.slug ?? '',
  description: props.restaurant?.description ?? '',
  cuisine: props.restaurant?.cuisine ?? '',
  location: props.restaurant?.location ?? '',
  dress_code: props.restaurant?.dress_code ?? '',
  reservation_url: props.restaurant?.reservation_url ?? '',
  status: props.restaurant?.status ?? 'draft',
});

const submit = () => {
  if (isEdit) {
    form.put(`/admin/restaurants/${props.restaurant!.id}`);
  } else {
    form.post('/admin/restaurants');
  }
};

// --- Menu editor (only shown once the restaurant exists) ---
const menuForm = reactive({
  categories: (props.restaurant?.active_menu?.categories ?? []).map((c) => ({
    id: c.id,
    name: c.name,
    items: c.items.map((i) => ({
      id: i.id,
      name: i.name,
      description: i.description ?? '',
      price: i.price,
      is_available: i.is_available,
    })),
  })),
});

const addCategory = () => {
  menuForm.categories.push({ id: undefined, name: '', items: [] });
};

const removeCategory = (index: number) => {
  menuForm.categories.splice(index, 1);
};

const addItem = (categoryIndex: number) => {
  menuForm.categories[categoryIndex].items.push({
    id: undefined, name: '', description: '', price: '0.00', is_available: true,
  });
};

const removeItem = (categoryIndex: number, itemIndex: number) => {
  menuForm.categories[categoryIndex].items.splice(itemIndex, 1);
};

const menuSaving = ref(false);
const saveMenu = () => {
  if (!props.restaurant) return;
  menuSaving.value = true;
  router.put(`/admin/restaurants/${props.restaurant.id}/menu`, menuForm, {
    onFinish: () => { menuSaving.value = false; },
  });
};
</script>

<template>
  <div class="max-w-2xl space-y-8">
    <div>
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-slate-800">
          {{ isEdit ? 'Edit Restaurant' : 'Add Restaurant' }}
        </h1>
        <!--
          Entry point into the shared Page Builder engine, scoped to this
          restaurant's own entity presentation - see
          RestaurantPresentationController::edit(). Only shown once the
          restaurant exists (can't customize a guest page for a restaurant
          that hasn't been created yet).
        -->
        <Link
          v-if="isEdit"
          :href="`/admin/restaurants/${props.restaurant!.id}/presentation/builder`"
          class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
        >
          🎨 Customize Guest Page
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input v-model="form.name" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Slug</label>
            <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Cuisine</label>
            <input v-model="form.cuisine" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
            <input v-model="form.location" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Dress code</label>
            <input v-model="form.dress_code" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Reservation URL</label>
            <input v-model="form.reservation_url" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
          <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div class="flex justify-end pt-2">
          <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
            {{ isEdit ? 'Save changes' : 'Create restaurant' }}
          </button>
        </div>
      </form>
    </div>

    <div v-if="isEdit">
      <h2 class="text-lg font-semibold text-slate-800 mb-4">Menu</h2>

      <div class="space-y-4">
        <div
          v-for="(category, ci) in menuForm.categories"
          :key="ci"
          class="rounded-lg border border-slate-200 bg-white p-4"
        >
          <div class="flex items-center gap-2 mb-3">
            <input v-model="category.name" placeholder="Category name" class="flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium" />
            <button class="text-red-500 hover:text-red-700 text-sm" @click="removeCategory(ci)">Remove</button>
          </div>

          <div v-for="(item, ii) in category.items" :key="ii" class="flex items-center gap-2 mb-2">
            <input v-model="item.name" placeholder="Item name" class="flex-1 rounded-md border border-slate-300 px-2 py-1 text-sm" />
            <input v-model="item.price" placeholder="Price" class="w-20 rounded-md border border-slate-300 px-2 py-1 text-sm" />
            <button class="text-red-400 hover:text-red-600 text-xs" @click="removeItem(ci, ii)">✕</button>
          </div>

          <button class="text-sm text-slate-500 hover:text-slate-800" @click="addItem(ci)">+ Add item</button>
        </div>

        <button class="text-sm text-slate-500 hover:text-slate-800" @click="addCategory">+ Add category</button>

        <div class="flex justify-end pt-2">
          <button :disabled="menuSaving" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50" @click="saveMenu">
            Save menu
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
