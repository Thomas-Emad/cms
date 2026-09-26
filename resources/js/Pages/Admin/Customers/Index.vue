<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/i18n';
import { AdminBadge, AdminButton, CreateButton, EditButton, PageHeader, ShowButton } from '@/Components/Admin';

defineOptions({ layout: AdminLayout });

interface CustomerItem {
  id: number;
  name: string;
  slug: string;
  domain: string | null;
  status: 'active' | 'suspended' | 'draft';
  users_count: number;
  created_at: string;
  primary_admin: {
    id: number;
    name: string;
    email: string;
  } | null;
}

interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  customers: Paginated<CustomerItem>;
  filters: { q: string | null; status: string | null };
  stats: { total: number; active: number; suspended: number; draft: number };
}>();

const { t } = useI18n();

const search = ref(props.filters.q ?? '');
const selectedStatus = ref(props.filters.status ?? '');

function applyFilters() {
  router.get(
    '/admin/customers',
    {
      q: search.value || undefined,
      status: selectedStatus.value || undefined,
    },
    { preserveState: true, replace: true }
  );
}

function resetFilters() {
  search.value = '';
  selectedStatus.value = '';
  applyFilters();
}

function toggleStatus(customer: CustomerItem) {
  const newStatus = customer.status === 'suspended' ? 'active' : 'suspended';
  const confirmMsg =
    newStatus === 'suspended'
      ? `Are you sure you want to suspend "${customer.name}"? Tenant access will be blocked.`
      : `Reactivate "${customer.name}"?`;

  if (confirm(confirmMsg)) {
    router.patch(`/admin/customers/${customer.id}/status`, { status: newStatus }, { preserveScroll: true });
  }
}
</script>

<template>
  <div>
    <PageHeader
      :title="t('customers.title', undefined, 'Platform Customers / Hotels')"
      :subtitle="t('customers.subtitle', undefined, 'Manage multi-tenant hotels, customer domains, and platform tenant lifecycles.')"
    >
      <template #actions>
        <CreateButton href="/admin/customers/create">
          {{ t('customers.create', undefined, '+ New Hotel Customer') }}
        </CreateButton>
      </template>
    </PageHeader>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Hotels</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ stats.total }}</div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-emerald-600 uppercase tracking-wider">Active</div>
        <div class="mt-1 text-2xl font-bold text-emerald-700">{{ stats.active }}</div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-rose-500 uppercase tracking-wider">Suspended</div>
        <div class="mt-1 text-2xl font-bold text-rose-600">{{ stats.suspended }}</div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="text-xs font-medium text-amber-500 uppercase tracking-wider">Draft</div>
        <div class="mt-1 text-2xl font-bold text-amber-600">{{ stats.draft }}</div>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs mb-6 flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex-1 w-full sm:w-auto flex flex-col sm:flex-row gap-3 items-center">
        <div class="relative w-full sm:w-72">
          <input
            v-model="search"
            type="text"
            placeholder="Search by hotel name, slug, domain..."
            class="w-full text-xs rounded-lg border border-slate-200 pl-8 pr-3 py-2 text-slate-800 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
            @keyup.enter="applyFilters"
          />
          <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
        </div>

        <select
          v-model="selectedStatus"
          class="w-full sm:w-44 text-xs rounded-lg border border-slate-200 px-3 py-2 text-slate-700 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
          @change="applyFilters"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
          <option value="draft">Draft</option>
        </select>

        <AdminButton variant="secondary" size="sm" @click="applyFilters">Filter</AdminButton>
        <button
          v-if="search || selectedStatus"
          type="button"
          class="text-xs text-slate-500 hover:text-slate-800 underline"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-start text-xs text-slate-600">
          <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 font-semibold uppercase tracking-wider text-[11px]">
            <tr>
              <th class="px-4 py-3 text-start">Hotel / Customer</th>
              <th class="px-4 py-3 text-start">Domain / Subdomain</th>
              <th class="px-4 py-3 text-start">Status</th>
              <th class="px-4 py-3 text-start">Primary Admin</th>
              <th class="px-4 py-3 text-center">Users</th>
              <th class="px-4 py-3 text-start">Created</th>
              <th class="px-4 py-3 text-end">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="customer in customers.data"
              :key="customer.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-4 py-3">
                <Link
                  :href="`/admin/customers/${customer.id}`"
                  class="font-semibold text-slate-800 hover:text-emerald-600 transition-colors"
                >
                  {{ customer.name }}
                </Link>
                <div class="text-[11px] text-slate-400 font-mono mt-0.5">slug: {{ customer.slug }}</div>
              </td>

              <td class="px-4 py-3">
                <div v-if="customer.domain" class="flex items-center gap-1.5 font-mono text-[11px] text-slate-700">
                  <span class="text-emerald-500">🌐</span>
                  <span>{{ customer.domain }}</span>
                </div>
                <div v-else class="text-[11px] text-slate-400 font-mono">
                  {{ customer.slug }}.[host]
                </div>
              </td>

              <td class="px-4 py-3">
                <AdminBadge
                  :variant="customer.status === 'active' ? 'published' : customer.status === 'suspended' ? 'archived' : 'draft'"
                >
                  {{ customer.status }}
                </AdminBadge>
              </td>

              <td class="px-4 py-3">
                <div v-if="customer.primary_admin">
                  <div class="font-medium text-slate-700">{{ customer.primary_admin.name }}</div>
                  <div class="text-[11px] text-slate-400">{{ customer.primary_admin.email }}</div>
                </div>
                <div v-else class="text-slate-400 italic">No admin assigned</div>
              </td>

              <td class="px-4 py-3 text-center font-medium text-slate-700">
                {{ customer.users_count }}
              </td>

              <td class="px-4 py-3 text-slate-400 text-[11px]">
                {{ customer.created_at }}
              </td>

              <td class="px-4 py-3 text-end">
                <div class="flex items-center justify-end gap-1.5">
                  <ShowButton :href="`/admin/customers/${customer.id}`" />
                  <EditButton :href="`/admin/customers/${customer.id}/edit`" />

                  <button
                    type="button"
                    class="px-2 py-1 rounded text-[11px] font-medium transition-colors"
                    :class="customer.status === 'suspended'
                      ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                      : 'bg-rose-50 text-rose-600 hover:bg-rose-100'"
                    @click="toggleStatus(customer)"
                  >
                    {{ customer.status === 'suspended' ? 'Activate' : 'Suspend' }}
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="customers.data.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                No customer hotels found matching your criteria.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="customers.links && customers.links.length > 3"
        class="px-4 py-3 border-t border-slate-100 flex items-center justify-between"
      >
        <div class="text-xs text-slate-500">
          Showing {{ customers.data.length }} of {{ customers.total }} hotels
        </div>
        <div class="flex items-center gap-1">
          <component
            :is="link.url ? Link : 'span'"
            v-for="(link, i) in customers.links"
            :key="i"
            :href="link.url ?? undefined"
            class="px-2.5 py-1 text-xs rounded-md border transition-colors"
            :class="link.active
              ? 'bg-emerald-600 text-white border-emerald-600 font-semibold'
              : link.url
                ? 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                : 'bg-slate-50 text-slate-300 border-slate-200 cursor-not-allowed'"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </div>
</template>
