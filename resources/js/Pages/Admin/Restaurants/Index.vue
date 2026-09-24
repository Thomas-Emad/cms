<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';
import type { Restaurant } from '@/types/restaurant';
import type { Paginated } from '@/types/facility';
import { AdminTable, CreateButton, EditButton, DeleteButton, AdminBadge } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

defineProps<{
    restaurants: Paginated<Restaurant>;
}>();

const { t } = useI18n();

const destroy = (restaurant: Restaurant) => {
    router.delete(`/admin/restaurants/${restaurant.id}`);
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.restaurants.title', undefined, 'Restaurants & Dining') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ t('admin.restaurants.subtitle', undefined, 'Manage hotel restaurants, bars, and dining venues.') }}</p>
            </div>
            <CreateButton href="/admin/restaurants/create">
                {{ t('admin.restaurants.create', undefined, '+ Add Restaurant') }}
            </CreateButton>
        </div>

        <AdminTable
            :items="restaurants.data"
            :empty-message="t('admin.common.empty', undefined, 'Nothing here yet — add your first one.')"
        >
            <template #header>
                <tr>
                    <th class="px-4 py-3 text-start">{{ t('common.name', undefined, 'Name') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('restaurants.cuisine', undefined, 'Cuisine') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('common.status_label', undefined, 'Status') }}</th>
                    <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
                </tr>
            </template>

            <tr
                v-for="restaurant in restaurants.data"
                :key="restaurant.id"
                class="hover:bg-slate-50/70 transition-colors"
            >
                <td class="px-4 py-3 font-medium text-slate-800">{{ restaurant.name }}</td>
                <td class="px-4 py-3 text-slate-500">{{ restaurant.cuisine || '—' }}</td>
                <td class="px-4 py-3">
                    <AdminBadge :variant="restaurant.status === 'published' ? 'published' : 'draft'" dot>
                        {{ t(`admin.status.${restaurant.status}`, undefined, restaurant.status) }}
                    </AdminBadge>
                </td>
                <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
                    <EditButton :href="`/admin/restaurants/${restaurant.id}/edit`" />
                    <DeleteButton
                        :confirm-message="`${t('admin.common.delete_confirm', undefined, 'Delete')} '${restaurant.name}'? ${t('admin.common.cannot_be_undone', undefined, 'This cannot be undone.')}`"
                        @confirm="destroy(restaurant)"
                    />
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
