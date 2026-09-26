<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/i18n';

defineOptions({ layout: AdminLayout });

interface PrimaryAdmin {
  name: string;
  email: string;
}

interface RecentHotel {
  id: number;
  name: string;
  slug: string;
  domain: string | null;
  status: 'active' | 'suspended' | 'draft';
  branches_count: number;
  primary_admin: PrimaryAdmin | null;
  created_at: string | null;
}

interface BranchWithDomain {
  id: number;
  name: string;
  slug: string;
  domain: string;
  hotel_id: number;
  hotel_name: string;
  city: string | null;
  is_main: boolean;
  status: string;
}

interface Metrics {
  total_hotels: number;
  active_hotels: number;
  suspended_hotels: number;
  draft_hotels: number;
  total_branches: number;
  total_users: number;
}

defineProps<{
  metrics: Metrics;
  recent_hotels: RecentHotel[];
  branches_with_domains: BranchWithDomain[];
}>();

const { locale } = useI18n();

function statusBadgeClass(status: string): string {
  switch (status) {
    case 'active':
    case 'published':
      return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    case 'suspended':
      return 'bg-rose-100 text-rose-800 border-rose-200';
    default:
      return 'bg-amber-100 text-amber-800 border-amber-200';
  }
}
</script>

<template>
  <div class="space-y-6">
    <Head :title="locale === 'ar' ? 'لوحة تحكم المنصة - المشرف العام' : 'Platform Dashboard - Super Admin'" />

    <!-- Top Banner -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-2">
          <span>🛡️</span>
          <span>{{ locale === 'ar' ? 'نطاق المشرف العام (SaaS Platform)' : 'SaaS Super Admin Platform' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
          {{ locale === 'ar' ? 'لوحة تحكم المنصة السحابية' : 'Platform Administration' }}
        </h1>
        <p class="mt-1 text-sm text-slate-500 max-w-2xl">
          {{ locale === 'ar'
            ? 'إدارة حسابات الفنادق المشتركة، تعيين نطاقات الفروع، ومتابعة حالة المستأجرين والمديرين عبر النظام بالكامل.'
            : 'Manage multi-tenant hotel accounts, branch domains, subscription status, and tenant access across the entire SaaS platform.' }}
        </p>
      </div>

      <div class="flex items-center gap-3 shrink-0">
        <Link
          href="/admin/customers"
          class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
        >
          <span>🏨</span>
          <span>{{ locale === 'ar' ? 'كافة الفنادق' : 'All Accounts' }}</span>
        </Link>
        <Link
          href="/admin/customers/create"
          class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors"
        >
          <span>➕</span>
          <span>{{ locale === 'ar' ? 'إضافة فندق جديد' : 'New Customer / Hotel' }}</span>
        </Link>
      </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
          {{ locale === 'ar' ? 'إجمالي الفنادق' : 'Total Hotels' }}
        </div>
        <div class="mt-2 text-2xl font-bold text-slate-800">{{ metrics.total_hotels }}</div>
        <div class="mt-1 text-xs text-slate-400">{{ locale === 'ar' ? 'حسابات مشتركة' : 'Tenant accounts' }}</div>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <div class="text-xs font-medium text-emerald-600 uppercase tracking-wider">
          {{ locale === 'ar' ? 'فنادق نشطة' : 'Active Hotels' }}
        </div>
        <div class="mt-2 text-2xl font-bold text-emerald-600">{{ metrics.active_hotels }}</div>
        <div class="mt-1 text-xs text-slate-400">{{ locale === 'ar' ? 'تعمل ومتاحة للجمهور' : 'Serving traffic' }}</div>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <div class="text-xs font-medium text-rose-600 uppercase tracking-wider">
          {{ locale === 'ar' ? 'فنادق موقوفة' : 'Suspended' }}
        </div>
        <div class="mt-2 text-2xl font-bold text-rose-600">{{ metrics.suspended_hotels }}</div>
        <div class="mt-1 text-xs text-slate-400">{{ locale === 'ar' ? 'محجوبة عن الجمهور' : 'Access blocked' }}</div>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
          {{ locale === 'ar' ? 'إجمالي الفروع' : 'Total Branches' }}
        </div>
        <div class="mt-2 text-2xl font-bold text-indigo-600">{{ metrics.total_branches }}</div>
        <div class="mt-1 text-xs text-slate-400">{{ locale === 'ar' ? 'فروع الفنادق' : 'Across all hotels' }}</div>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
          {{ locale === 'ar' ? 'نطاقات مخصصة' : 'Branch Domains' }}
        </div>
        <div class="mt-2 text-2xl font-bold text-cyan-600">{{ branches_with_domains.length }}</div>
        <div class="mt-1 text-xs text-slate-400">{{ locale === 'ar' ? 'نطاقات نشطة' : 'Mapped domains' }}</div>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
        <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">
          {{ locale === 'ar' ? 'المستخدمون' : 'Tenant Users' }}
        </div>
        <div class="mt-2 text-2xl font-bold text-slate-800">{{ metrics.total_users }}</div>
        <div class="mt-1 text-xs text-slate-400">{{ locale === 'ar' ? 'مديرون وموظفون' : 'Admins & Staff' }}</div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Hotels Table (2 cols on large screen) -->
      <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-slate-800">
              {{ locale === 'ar' ? 'أحدث حسابات الفنادق' : 'Recent Hotel Accounts' }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              {{ locale === 'ar' ? 'عرض حسابات الفنادق وحالة النطاقات والمديرين المسؤولين' : 'Overview of hotel tenants and their domain assignments' }}
            </p>
          </div>
          <Link
            href="/admin/customers"
            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline"
          >
            {{ locale === 'ar' ? 'عرض الكل' : 'View all accounts' }} →
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-start text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4 text-start">{{ locale === 'ar' ? 'الفندق' : 'Hotel' }}</th>
                <th class="py-3 px-4 text-start">{{ locale === 'ar' ? 'النطاق الرئيسي' : 'Main Domain' }}</th>
                <th class="py-3 px-4 text-start">{{ locale === 'ar' ? 'الفروع' : 'Branches' }}</th>
                <th class="py-3 px-4 text-start">{{ locale === 'ar' ? 'الحالة' : 'Status' }}</th>
                <th class="py-3 px-4 text-end">{{ locale === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="hotel in recent_hotels"
                :key="hotel.id"
                class="hover:bg-slate-50/80 transition-colors"
              >
                <td class="py-3 px-4">
                  <div class="font-medium text-slate-800">{{ hotel.name }}</div>
                  <div class="text-xs text-slate-400">slug: {{ hotel.slug }}</div>
                </td>
                <td class="py-3 px-4 text-slate-600">
                  <a
                    v-if="hotel.domain"
                    :href="`http://${hotel.domain}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 font-mono text-xs text-emerald-600 hover:underline"
                  >
                    <span>{{ hotel.domain }}</span>
                    <span class="text-slate-400 text-[10px]">↗</span>
                  </a>
                  <span v-else class="text-xs text-slate-400 italic">
                    {{ locale === 'ar' ? 'غير معين' : 'Not configured' }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                    {{ hotel.branches_count }} {{ locale === 'ar' ? 'فرع' : 'branch(es)' }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span
                    :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border', statusBadgeClass(hotel.status)]"
                  >
                    {{ hotel.status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-end">
                  <div class="inline-flex items-center gap-2">
                    <Link
                      :href="`/admin/customers/${hotel.id}`"
                      class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition-colors"
                    >
                      {{ locale === 'ar' ? 'إدارة' : 'Manage' }}
                    </Link>
                    <Link
                      :href="`/admin/customers/${hotel.id}/edit`"
                      class="px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded transition-colors"
                    >
                      {{ locale === 'ar' ? 'تعديل' : 'Edit' }}
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Branch Domains Quick Mapping (1 col on large screen) -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-200">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">
              {{ locale === 'ar' ? 'نطاقات الفروع النشطة' : 'Active Branch Domains' }}
            </h2>
            <span class="text-xs bg-cyan-50 text-cyan-700 border border-cyan-200 px-2 py-0.5 rounded-full font-semibold">
              {{ branches_with_domains.length }}
            </span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">
            {{ locale === 'ar' ? 'الفروع التي تملك نطاقات مخصصة أو فرعية مستقلة' : 'Branches mapped to specific custom domains / subdomains' }}
          </p>
        </div>

        <div class="p-4 flex-1 overflow-y-auto space-y-3">
          <div
            v-if="branches_with_domains.length === 0"
            class="text-center py-8 text-sm text-slate-400"
          >
            {{ locale === 'ar' ? 'لا توجد نطاقات فروع مخصصة حالياً.' : 'No branches have custom domains configured yet.' }}
          </div>

          <div
            v-for="branch in branches_with_domains"
            :key="branch.id"
            class="p-3 rounded-lg border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition-colors"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div class="font-medium text-sm text-slate-800 truncate">{{ branch.name }}</div>
                <div class="text-xs text-slate-500 truncate">
                  {{ branch.hotel_name }} <span v-if="branch.city">({{ branch.city }})</span>
                </div>
              </div>
              <span
                v-if="branch.is_main"
                class="shrink-0 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded bg-amber-50 text-amber-700 border border-amber-200"
              >
                {{ locale === 'ar' ? 'الرئيسي' : 'Main' }}
              </span>
            </div>

            <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
              <a
                :href="`http://${branch.domain}`"
                target="_blank"
                rel="noopener noreferrer"
                class="font-mono text-xs text-indigo-600 hover:underline truncate inline-flex items-center gap-1"
                :title="`Open http://${branch.domain}`"
              >
                <span>🌐</span>
                <span class="truncate">{{ branch.domain }}</span>
                <span class="text-slate-400 text-[10px]">↗</span>
              </a>

              <Link
                :href="`/admin/customers/${branch.hotel_id}`"
                class="text-xs text-slate-500 hover:text-slate-800 shrink-0"
              >
                {{ locale === 'ar' ? 'الحساب' : 'Account' }} →
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
