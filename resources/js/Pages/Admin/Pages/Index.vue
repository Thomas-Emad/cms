<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineOptions({ layout: AdminLayout });

interface PageRow {
  id: number;
  name: string;
  slug: string;
  status: 'draft' | 'published' | 'archived';
  is_home: boolean;
  published_version_id: number | null;
  updated_at: string;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  pages: Paginated<PageRow>;
  filters: { q: string | null };
}>();

const search = ref(props.filters.q ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/admin/pages', { q: value || undefined }, { preserveState: true, replace: true });
  }, 300);
});

function statusBadgeClass(status: PageRow['status']): string {
  if (status === 'published') return 'bg-emerald-50 text-emerald-700';
  if (status === 'archived') return 'bg-slate-100 text-slate-400';
  return 'bg-amber-50 text-amber-700';
}

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<template>
  <div>
    <div class="flex items-start justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-slate-800">{{ $t('admin.pages.title', 'Pages') }}</h1>
        <p class="mt-1 text-sm text-slate-500">
          {{ $t('admin.pages.subtitle', "Build and manage the pages that make up your hotel's guest website.") }}
        </p>
      </div>
      <Link
        href="/admin/pages/create"
        class="shrink-0 rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900"
      >
        {{ $t('admin.pages.create', '+ Create Page') }}
      </Link>
    </div>

    <div v-if="pages.data.length || filters.q" class="mb-4">
      <input
        v-model="search"
        type="text"
        :placeholder="$t('admin.pages.search_placeholder', 'Search pages by name or slug…')"
        class="w-full max-w-sm rounded-md border border-slate-300 px-3 py-2 text-sm"
      />
    </div>

    <div
      v-if="!pages.data.length && !filters.q"
      class="rounded-lg border border-dashed border-slate-300 bg-white p-16 text-center"
    >
      <p class="text-slate-600 font-medium">{{ $t('admin.pages.empty_title', 'No pages yet') }}</p>
      <p class="mt-1 text-sm text-slate-400">
        {{ $t('admin.pages.empty_desc', 'Create your first page to start building your hotel website.') }}
      </p>
      <Link
        href="/admin/pages/create"
        class="mt-4 inline-block rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900"
      >
        {{ $t('admin.pages.create', '+ Create Page') }}
      </Link>
    </div>

    <div
      v-else-if="!pages.data.length && filters.q"
      class="rounded-lg border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-400"
    >
      {{ $t('admin.pages.no_match', 'No pages match') }} "{{ filters.q }}".
    </div>

    <div v-else class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-start text-xs uppercase text-slate-400">
          <tr>
            <th class="px-4 py-2 text-start">{{ $t('common.name') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('admin.pages.slug', 'Slug') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('common.status') }}</th>
            <th class="px-4 py-2 text-start">{{ $t('admin.pages.updated', 'Updated') }}</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="pageRow in pages.data" :key="pageRow.id" class="hover:bg-slate-50">
            <td class="px-4 py-3 font-medium text-slate-700">
              {{ pageRow.name }}
              <span
                v-if="pageRow.is_home"
                class="ms-2 rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-600"
                :title="$t('admin.pages.is_home_tooltip', 'This is the homepage')"
              >
                {{ $t('common.home') }}
              </span>
            </td>
            <td class="px-4 py-3 text-slate-500 font-mono text-xs">
              {{ pageRow.is_home ? '/' : `/pages/${pageRow.slug}` }}
            </td>
            <td class="px-4 py-3">
              <span class="rounded-full px-2 py-0.5 text-xs" :class="statusBadgeClass(pageRow.status)">
                {{ $t(`admin.status.${pageRow.status}`, pageRow.status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-slate-400">{{ formatDate(pageRow.updated_at) }}</td>
            <td class="px-4 py-3 text-end space-x-3 rtl:space-x-reverse whitespace-nowrap">
              <Link
                :href="`/admin/pages/${pageRow.id}/builder`"
                class="font-medium hover:underline"
                style="color: var(--color-primary, #1F4B5A)"
              >
                {{ $t('admin.pages.open_builder', 'Open Builder') }}
              </Link>
              <Link
                v-if="pageRow.status !== 'draft'"
                :href="`/admin/pages/${pageRow.id}/preview`"
                class="text-slate-500 hover:text-slate-800"
              >
                {{ $t('admin.pages.preview', 'Preview') }}
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
