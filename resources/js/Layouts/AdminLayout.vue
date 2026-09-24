<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import type { SharedPageProps } from '@/types/hotel';
import { useI18n } from '@/i18n';
import AdminHeader from '@/Components/Admin/AdminHeader.vue';
import AdminFooter from '@/Components/Admin/AdminFooter.vue';
import { useAdminTheme } from '@/composables/useAdminTheme';

const page = usePage<SharedPageProps>();
const { t } = useI18n();
const { initTheme } = useAdminTheme();
const mobileSidebarOpen = ref(false);

onMounted(() => {
  initTheme();
});

const nav = computed(() => [
  { key: 'nav.dashboard', label: 'Dashboard', href: '/admin/dashboard', icon: '📊' },
  {
    key: 'nav.hotel',
    label: 'Hotel',
    children: [
      { key: 'nav.branches', label: 'Branches', href: '/admin/branches' },
      { key: 'nav.theme', label: 'Theme', href: '/admin/theme', disabled: false },
      { key: 'nav.settings', label: 'Settings', href: '/admin/settings', disabled: false },
    ],
  },

  {
    key: 'nav.content',
    label: 'Content',
    children: [
      { key: 'nav.facilities', label: 'Facilities', href: '/admin/facilities' },
      { key: 'nav.meeting_rooms', label: 'Meeting Rooms', href: '/admin/facilities?category=meeting' },
      { key: 'nav.rooms', label: 'Rooms & Suites', href: '/admin/rooms' },
      { key: 'nav.timing', label: 'Timing', href: '/admin/timing' },
      { key: 'nav.short_calls', label: 'Short Calls', href: '/admin/short-calls' },
      { key: 'nav.gallery', label: 'Gallery', href: '/admin/gallery' },
      { key: 'nav.hotel_map', label: 'Hotel Map', href: '/admin/map/builder' },
      { key: 'nav.restaurants', label: 'Restaurants', href: '/admin/restaurants' },
      { key: 'nav.services', label: 'Services', href: '/admin/services' },
      { key: 'nav.events', label: 'Events', href: '/admin/events' },
      { key: 'nav.offers', label: 'Offers', href: '/admin/offers' },
      { key: 'nav.experiences', label: 'Experiences', href: '/admin/experiences' },
    ],
  },
  {
    key: 'nav.website',
    label: 'Website',
    children: [
      { key: 'nav.pages', label: 'Pages', href: '/admin/pages' },
      { key: 'nav.guest_layout', label: 'Guest Layout', href: '/admin/layout', disabled: false },
      { key: 'nav.theme', label: 'Theme', href: '/admin/theme', disabled: false },
    ],
  },
  {
    key: 'nav.media',
    label: 'Media',
    children: [{ key: 'nav.media_library', label: 'Media Library', href: '#', disabled: true }],
  },
]);

/**
 * Active-state match: a link is "active" if the current URL starts with
 * its href - this correctly highlights "Pages" while on /admin/pages,
 * /admin/pages/create, or /admin/pages/{id}/builder, not just an exact
 * match on the index route.
 */
function isActive(href: string): boolean {
  if (href === '#' || href === '') return false;
  const current = page.url.split('?')[0];
  return current === href || current.startsWith(`${href}/`);
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50 text-slate-900">
    <!-- Mobile Sidebar Drawer (Off-canvas) -->
    <div
      v-if="mobileSidebarOpen"
      class="fixed inset-0 z-50 flex md:hidden"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="mobileSidebarOpen = false"
      />
      <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white pt-5 pb-4 px-4 shadow-xl z-10">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
          <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: var(--admin-primary)" />
            <span class="font-semibold text-slate-800 truncate">{{ page.props.hotel?.name ?? 'Admin' }}</span>
          </div>
          <button
            type="button"
            class="p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100"
            @click="mobileSidebarOpen = false"
          >
            <span class="sr-only">Close sidebar</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <nav class="mt-4 flex-1 overflow-y-auto space-y-4">
          <div v-for="item in nav" :key="item.key">
            <Link
              v-if="item.href"
              :href="item.href"
              class="block rounded-md px-2.5 py-1.5 text-sm font-medium transition-colors"
              :style="isActive(item.href) ? { backgroundColor: 'var(--admin-sidebar-active-bg)', color: 'var(--admin-sidebar-active-text)', fontWeight: 600 } : {}"
              :class="!isActive(item.href) ? 'text-slate-700 hover:bg-slate-100' : ''"
              @click="mobileSidebarOpen = false"
            >
              {{ item.icon }} {{ t(item.key, undefined, item.label) }}
            </Link>

            <div v-else>
              <div class="px-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                {{ t(item.key, undefined, item.label) }}
              </div>
              <component
                :is="child.disabled ? 'span' : Link"
                v-for="child in item.children"
                :key="child.key"
                :href="child.disabled ? undefined : child.href"
                class="block rounded-md px-2.5 py-1.5 text-sm transition-colors"
                :style="!child.disabled && isActive(child.href) ? { backgroundColor: 'var(--admin-sidebar-active-bg)', color: 'var(--admin-sidebar-active-text)', fontWeight: 600 } : {}"
                :class="child.disabled
                  ? 'text-slate-300 cursor-not-allowed'
                  : (!isActive(child.href) ? 'text-slate-600 hover:bg-slate-100' : '')"
                @click="!child.disabled ? (mobileSidebarOpen = false) : undefined"
              >
                {{ t(child.key, undefined, child.label) }}
              </component>
            </div>
          </div>
        </nav>
      </div>
    </div>

    <!-- Desktop Aside Navigation -->
    <aside class="w-64 shrink-0 border-e border-slate-200 bg-white px-4 py-6 hidden md:block">
      <div class="mb-6 flex items-center gap-2">
        <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: var(--admin-primary)" />
        <div class="font-semibold text-slate-800 truncate">
          {{ page.props.hotel?.name ?? 'Admin' }}
        </div>
      </div>

      <nav class="space-y-4">
        <div v-for="item in nav" :key="item.key">
          <Link
            v-if="item.href"
            :href="item.href"
            class="block rounded-md px-2.5 py-1.5 text-sm font-medium transition-colors"
            :style="isActive(item.href) ? { backgroundColor: 'var(--admin-sidebar-active-bg)', color: 'var(--admin-sidebar-active-text)', fontWeight: 600 } : {}"
            :class="!isActive(item.href) ? 'text-slate-700 hover:bg-slate-100' : ''"
          >
            {{ item.icon }} {{ t(item.key, undefined, item.label) }}
          </Link>

          <div v-else>
            <div class="px-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
              {{ t(item.key, undefined, item.label) }}
            </div>
            <component
              :is="child.disabled ? 'span' : Link"
              v-for="child in item.children"
              :key="child.key"
              :href="child.disabled ? undefined : child.href"
              class="block rounded-md px-2.5 py-1.5 text-sm transition-colors"
              :style="!child.disabled && isActive(child.href) ? { backgroundColor: 'var(--admin-sidebar-active-bg)', color: 'var(--admin-sidebar-active-text)', fontWeight: 600 } : {}"
              :class="child.disabled
                ? 'text-slate-300 cursor-not-allowed'
                : (!isActive(child.href) ? 'text-slate-600 hover:bg-slate-100' : '')"
            >
              {{ t(child.key, undefined, child.label) }}
            </component>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <AdminHeader @toggle-sidebar="mobileSidebarOpen = !mobileSidebarOpen" />

      <main class="flex-1 p-4 sm:p-6">
        <slot />
      </main>

      <AdminFooter :hotel-name="page.props.hotel?.name ?? 'Grand Horizon'" />
    </div>
  </div>
</template>
