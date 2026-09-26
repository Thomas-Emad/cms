<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useI18n } from '@/i18n';
import { AdminTable, CreateButton, EditButton, ShowButton, AdminInput, AdminBadge, BranchFilter } from '@/Components/Admin';
import { formatDate } from '@/lib/formatters';

defineOptions({ layout: AdminLayout });

interface PageRow {
  id: number;
  name: string;
  slug: string;
  status: 'draft' | 'published' | 'archived';
  is_home: boolean;
  published_version_id: number | null;
  updated_at: string;
  hotel_branch_id?: number | null;
  branch?: { id: number; name: string; city?: string | null } | null;
}

interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
  pages: Paginated<PageRow>;
  filters: { q: string | null; branch_id?: number | null };
  selected_branch_id?: number | null;
}>();

const { t, locale } = useI18n();
const search = ref(props.filters.q ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/admin/pages', {
      q: value || undefined,
      branch_id: props.selected_branch_id || undefined,
    }, { preserveState: true, replace: true });
  }, 300);
});
</script>

<template>
  <div>
    <div class="flex items-start justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-slate-800">{{ t('admin.pages.title', undefined, 'Pages') }}</h1>
        <p class="mt-1 text-sm text-slate-500">
          {{ t('admin.pages.subtitle', undefined, "Build and manage the pages that make up your hotel's guest website.") }}
        </p>
      </div>
      <CreateButton href="/admin/pages/create">
        {{ t('admin.pages.create', undefined, '+ Create Page') }}
      </CreateButton>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
      <div class="max-w-sm w-full">
        <AdminInput
          v-model="search"
          type="text"
          :placeholder="t('admin.pages.search_placeholder', undefined, 'Search pages by name or slug…')"
          prefix="🔍"
        />
      </div>
      <BranchFilter
        :selected-branch-id="selected_branch_id"
        :extra-params="filters.q ? { q: filters.q } : {}"
      />
    </div>

    <AdminTable
      :items="pages.data"
      :empty-message="filters.q ? `${t('admin.pages.no_match', undefined, 'No pages match')} '${filters.q}'` : t('admin.pages.empty_title', undefined, 'No pages yet')"
      :empty-description="filters.q ? undefined : t('admin.pages.empty_desc', undefined, 'Create your first page to start building your hotel website.')"
    >
      <template #header>
        <tr>
          <th class="px-4 py-3 text-start">{{ t('common.name', undefined, 'Name') }}</th>
          <th class="px-4 py-3 text-start">{{ t('admin.branch', undefined, 'Branch') }}</th>
          <th class="px-4 py-3 text-start">{{ t('admin.pages.slug', undefined, 'Slug') }}</th>
          <th class="px-4 py-3 text-start">{{ t('common.status', undefined, 'Status') }}</th>
          <th class="px-4 py-3 text-start">{{ t('admin.pages.updated', undefined, 'Updated') }}</th>
          <th class="px-4 py-3 text-end">{{ t('common.actions', undefined, 'Actions') }}</th>
        </tr>
      </template>

      <tr
        v-for="pageRow in pages.data"
        :key="pageRow.id"
        class="hover:bg-slate-50/70 transition-colors"
      >
        <td class="px-4 py-3 font-medium text-slate-800">
          {{ pageRow.name }}
          <span
            v-if="pageRow.is_home"
            class="ms-2 rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-600 font-normal"
            :title="t('admin.pages.is_home_tooltip', undefined, 'This is the homepage')"
          >
            {{ t('common.home', undefined, 'Home') }}
          </span>
        </td>
        <td class="px-4 py-3">
          <AdminBadge v-if="(pageRow as any).branch" variant="info">
            📍 {{ (pageRow as any).branch.name }}
          </AdminBadge>
          <span v-else class="text-xs text-slate-400">
            {{ locale === 'ar' ? 'عام (كل الفروع)' : 'All Branches' }}
          </span>
        </td>
        <td class="px-4 py-3 text-slate-500 font-mono text-xs">
          {{ pageRow.is_home ? '/' : `/pages/${pageRow.slug}` }}
        </td>
        <td class="px-4 py-3">
          <AdminBadge :variant="pageRow.status" dot>
            {{ t(`admin.status.${pageRow.status}`, undefined, pageRow.status) }}
          </AdminBadge>
        </td>
        <td class="px-4 py-3 text-slate-400 text-xs">{{ formatDate(pageRow.updated_at) }}</td>
        <td class="px-4 py-3 text-end space-x-2 rtl:space-x-reverse whitespace-nowrap">
          <EditButton
            :href="`/admin/pages/${pageRow.id}/builder`"
            :label="t('admin.pages.open_builder', undefined, 'Open Builder')"
          />
          <ShowButton
            v-if="pageRow.status !== 'draft'"
            :href="`/admin/pages/${pageRow.id}/preview`"
            :label="t('admin.pages.preview', undefined, 'Preview')"
            target="_blank"
          />
        </td>
      </tr>
    </AdminTable>
  </div>
</template>
