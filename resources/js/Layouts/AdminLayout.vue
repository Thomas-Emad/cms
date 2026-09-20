<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedPageProps } from '@/types/hotel';

const page = usePage<SharedPageProps>();

const nav = [
  { label: 'Dashboard', href: '/admin/dashboard', icon: '📊' },
  {
    label: 'Hotel',
    children: [
      { label: 'Hotel Information', href: '#', disabled: true },
      { label: 'Theme', href: '#', disabled: true },
      { label: 'Settings', href: '#', disabled: true },
    ],
  },
  {
    label: 'Content',
    children: [
      { label: 'Facilities', href: '/admin/facilities' },
      { label: 'Meeting Rooms', href: '/admin/facilities?category=meeting' },
      { label: 'Rooms & Suites', href: '/admin/rooms' },
      { label: 'Timing', href: '/admin/timing' },
      { label: 'Short Calls', href: '/admin/short-calls' },
      { label: 'Gallery', href: '/admin/gallery' },
      { label: 'Restaurants', href: '/admin/restaurants' },
      { label: 'Services', href: '/admin/services' },
      { label: 'Events', href: '/admin/events' },
      { label: 'Offers', href: '/admin/offers' },
      { label: 'Experiences', href: '/admin/experiences' },
    ],
  },
  {
    label: 'Website',
    children: [
      { label: 'Pages', href: '/admin/pages' },
      { label: 'Navigation', href: '#', disabled: true },
    ],
  },
  {
    label: 'Media',
    children: [{ label: 'Media Library', href: '#', disabled: true }],
  },
];

/**
 * Active-state match: a link is "active" if the current URL starts with
 * its href - this correctly highlights "Pages" while on /admin/pages,
 * /admin/pages/create, or /admin/pages/{id}/builder, not just an exact
 * match on the index route.
 */
function isActive(href: string): boolean {
  if (href === '#') return false;
  const current = page.url.split('?')[0];
  return current === href || current.startsWith(`${href}/`);
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50 text-slate-900">
    <aside class="w-64 shrink-0 border-r border-slate-200 bg-white px-4 py-6 hidden md:block">
      <div class="mb-6 font-semibold text-slate-800">
        {{ page.props.hotel?.name ?? 'Admin' }}
      </div>

      <nav class="space-y-4">
        <div v-for="item in nav" :key="item.label">
          <Link
            v-if="item.href"
            :href="item.href"
            class="block rounded-md px-2 py-1.5 text-sm font-medium"
            :class="isActive(item.href) ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100'"
          >
            {{ item.icon }} {{ item.label }}
          </Link>

          <div v-else>
            <div class="px-2 text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
              {{ item.label }}
            </div>
            <component
              :is="child.disabled ? 'span' : Link"
              v-for="child in item.children"
              :key="child.label"
              :href="child.disabled ? undefined : child.href"
              class="block rounded-md px-2 py-1.5 text-sm"
              :class="child.disabled
                ? 'text-slate-300 cursor-not-allowed'
                : (isActive(child.href) ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-100')"
            >
              {{ child.label }}
            </component>
          </div>
        </div>
      </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      <header class="border-b border-slate-200 bg-white px-6 py-3 flex items-center justify-between">
        <span class="text-sm text-slate-500">Admin Dashboard</span>
        <div class="flex items-center gap-3">
          <span class="text-sm text-slate-700">{{ page.props.auth?.user?.name }}</span>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="text-sm text-slate-400 hover:text-slate-700"
          >
            Log out
          </Link>
        </div>
      </header>

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
