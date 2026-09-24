<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BilingualTabs from '@/Components/Admin/BilingualTabs.vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useI18n } from '@/i18n';
import type { Restaurant } from '@/types/restaurant';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
  restaurant: Restaurant | null;
}>();

const { t } = useI18n();
const isEdit = !!props.restaurant;
const activeTab = ref<'en' | 'ar'>('en');

const rawTranslations = (props.restaurant as any)?.translations_data?.ar ?? {};

const form = useForm({
  name: props.restaurant?.name ?? '',
  slug: props.restaurant?.slug ?? '',
  description: props.restaurant?.description ?? '',
  cuisine: props.restaurant?.cuisine ?? '',
  location: props.restaurant?.location ?? '',
  dress_code: props.restaurant?.dress_code ?? '',
  reservation_url: props.restaurant?.reservation_url ?? '',
  status: props.restaurant?.status ?? 'draft',
  translations: {
    ar: {
      name: rawTranslations.name ?? '',
      description: rawTranslations.description ?? '',
      cuisine: rawTranslations.cuisine ?? '',
      dress_code: rawTranslations.dress_code ?? '',
      location: rawTranslations.location ?? '',
    },
  },
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
  categories: (props.restaurant?.active_menu?.categories ?? []).map((c: any) => ({
    id: c.id,
    name: c.name,
    name_ar: c.translations_data?.ar?.name ?? '',
    items: (c.items ?? []).map((i: any) => ({
      id: i.id,
      name: i.name,
      name_ar: i.translations_data?.ar?.name ?? '',
      description: i.description ?? '',
      description_ar: i.translations_data?.ar?.description ?? '',
      price: i.price,
      is_available: i.is_available,
    })),
  })),
});

const addCategory = () => {
  menuForm.categories.push({ id: undefined, name: '', name_ar: '', items: [] });
};

const removeCategory = (index: number) => {
  menuForm.categories.splice(index, 1);
};

const addItem = (categoryIndex: number) => {
  menuForm.categories[categoryIndex].items.push({
    id: undefined, name: '', name_ar: '', description: '', description_ar: '', price: '0.00', is_available: true,
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
          {{ isEdit ? t('admin.edit_restaurant', undefined, 'Edit Restaurant') : t('admin.add_restaurant', undefined, 'Add Restaurant') }}
        </h1>
        <div class="flex items-center gap-3">
          <Link
            v-if="isEdit"
            :href="`/admin/restaurants/${props.restaurant!.id}/presentation/builder`"
            class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
          >
            {{ t('admin.customize_guest_page', undefined, '🎨 Customize Guest Page') }}
          </Link>
          <Link href="/admin/restaurants" class="text-sm text-slate-500 hover:text-slate-800">
            {{ t('admin.all_restaurants', undefined, '← All restaurants') }}
          </Link>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6">
        <BilingualTabs v-model="activeTab" />

        <!-- Arabic Translation Fields -->
        <div v-if="activeTab === 'ar'" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.name_ar', undefined, 'Name (AR) / الاسم بالعربية') }}
            </label>
            <input
              v-model="form.translations.ar.name"
              type="text"
              dir="rtl"
              :placeholder="form.name || 'اسم المطعم بالعربية...'"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            />
            <p class="mt-1 text-xs text-slate-400">EN: {{ form.name || '—' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.description_ar', undefined, 'Description (AR) / الوصف التفصيلي بالعربية') }}
            </label>
            <textarea
              v-model="form.translations.ar.description"
              rows="3"
              dir="rtl"
              :placeholder="form.description || 'وصف المطعم بالعربية...'"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('restaurants.cuisine', undefined, 'Cuisine') }} (AR)
              </label>
              <input
                v-model="form.translations.ar.cuisine"
                type="text"
                dir="rtl"
                :placeholder="form.cuisine || 'نوع المطبخ بالعربية...'"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('restaurants.location', undefined, 'Location') }} (AR)
              </label>
              <input
                v-model="form.translations.ar.location"
                type="text"
                dir="rtl"
                :placeholder="form.location || 'الموقع داخل الفندق...'"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('restaurants.dress_code', undefined, 'Dress code') }} (AR)
            </label>
            <input
              v-model="form.translations.ar.dress_code"
              type="text"
              dir="rtl"
              :placeholder="form.dress_code || 'قواعد اللباس بالعربية...'"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
        </div>

        <!-- English & Base Fields -->
        <div v-else class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('admin.common.name_en', undefined, 'Name (EN)') }}
              </label>
              <input v-model="form.name" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('admin.common.slug', undefined, 'Slug') }}
              </label>
              <input v-model="form.slug" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
              <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.description_en', undefined, 'Description') }}
            </label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('restaurants.cuisine', undefined, 'Cuisine') }}
              </label>
              <input v-model="form.cuisine" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('restaurants.location', undefined, 'Location') }}
              </label>
              <input v-model="form.location" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('restaurants.dress_code', undefined, 'Dress code') }}
              </label>
              <input v-model="form.dress_code" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ t('common.url', undefined, 'Reservation URL') }}
              </label>
              <input v-model="form.reservation_url" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              {{ t('admin.common.status', undefined, 'Status') }}
            </label>
            <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
              <option value="draft">{{ t('status.draft', undefined, 'Draft') }}</option>
              <option value="published">{{ t('status.published', undefined, 'Published') }}</option>
              <option value="archived">{{ t('status.archived', undefined, 'Archived') }}</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
          <button type="submit" :disabled="form.processing" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50">
            {{ isEdit ? t('admin.common.save', undefined, 'Save changes') : t('common.create', undefined, 'Create restaurant') }}
          </button>
        </div>
      </form>
    </div>

    <!-- Menu Editor -->
    <div v-if="isEdit">
      <h2 class="text-lg font-semibold text-slate-800 mb-4">
        {{ t('restaurants.menu', undefined, 'Menu') }}
      </h2>

      <div class="space-y-4">
        <div
          v-for="(category, ci) in menuForm.categories"
          :key="ci"
          class="rounded-lg border border-slate-200 bg-white p-4"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
            <input v-model="category.name" :placeholder="t('restaurants.category_name', undefined, 'Category name (EN)')" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium" />
            <div class="flex items-center gap-2">
              <input v-model="category.name_ar" dir="rtl" :placeholder="t('restaurants.category_name', undefined, 'Category name') + ' (العربية)'" class="flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium" />
              <button class="text-red-500 hover:text-red-700 text-sm shrink-0" @click="removeCategory(ci)">{{ t('common.delete', undefined, 'Remove') }}</button>
            </div>
          </div>

          <div v-for="(item, ii) in category.items" :key="ii" class="rounded-md border border-slate-100 bg-slate-50 p-2.5 mb-2">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-1.5">
              <input v-model="item.name" :placeholder="t('restaurants.item_name', undefined, 'Item name (EN)')" class="rounded-md border border-slate-300 px-2 py-1 text-sm bg-white" />
              <input v-model="item.name_ar" dir="rtl" :placeholder="t('restaurants.item_name', undefined, 'Item name') + ' (العربية)'" class="rounded-md border border-slate-300 px-2 py-1 text-sm bg-white" />
              <div class="flex items-center gap-2">
                <input v-model="item.price" :placeholder="t('restaurants.price', undefined, 'Price')" class="w-24 rounded-md border border-slate-300 px-2 py-1 text-sm bg-white" />
                <button class="text-red-400 hover:text-red-600 text-xs px-1" @click="removeItem(ci, ii)">✕</button>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <input v-model="item.description" :placeholder="t('restaurants.item_description', undefined, 'Description (EN)')" class="rounded-md border border-slate-300 px-2 py-1 text-xs bg-white" />
              <input v-model="item.description_ar" dir="rtl" :placeholder="t('restaurants.item_description', undefined, 'Description') + ' (العربية)'" class="rounded-md border border-slate-300 px-2 py-1 text-xs bg-white" />
            </div>
          </div>

          <button class="text-sm text-slate-500 hover:text-slate-800 pt-1" @click="addItem(ci)">
            {{ t('restaurants.add_item', undefined, '+ Add item') }}
          </button>
        </div>

        <button class="text-sm text-slate-500 hover:text-slate-800" @click="addCategory">
          {{ t('restaurants.add_category', undefined, '+ Add category') }}
        </button>

        <div class="flex justify-end pt-2">
          <button :disabled="menuSaving" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 disabled:opacity-50" @click="saveMenu">
            {{ menuSaving ? t('restaurants.saving_menu', undefined, 'Saving...') : t('restaurants.save_menu', undefined, 'Save menu') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
