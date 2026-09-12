import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
  router: { get: vi.fn() },
  Link: {
    props: ['href'],
    render() {
      return h('a', { href: this.href }, this.$slots.default?.());
    },
  },
  usePage: () => ({ props: { auth: { user: { name: 'Test Admin' } }, hotel: { name: 'Grand Horizon' } }, url: '/admin/pages' }),
}));

import Index from './Index.vue';
import { router } from '@inertiajs/vue3';

function mountIndex(pages: any[], filters: { q: string | null } = { q: null }) {
  return mount(Index, {
    props: {
      pages: { data: pages, links: [] },
      filters,
    },
  });
}

const samplePage = {
  id: 1,
  name: 'Homepage',
  slug: '',
  status: 'published' as const,
  is_home: true,
  published_version_id: 5,
  updated_at: '2026-01-15T10:00:00Z',
};

describe('Admin/Pages/Index.vue (real component)', () => {
  beforeEach(() => vi.clearAllMocks());

  it('renders the empty state with a Create Page call-to-action when there are no pages', () => {
    const wrapper = mountIndex([]);

    expect(wrapper.text()).toContain('No pages yet');
    expect(wrapper.text()).toContain('Create your first page to start building your hotel website.');
    const links = wrapper.findAll('a').filter((a) => a.text().includes('Create Page'));
    expect(links.length).toBeGreaterThan(0);
  });

  it('renders a list of pages with name, status badge, and home indicator', () => {
    const wrapper = mountIndex([
      samplePage,
      { ...samplePage, id: 2, name: 'About', slug: 'about', is_home: false, status: 'draft', published_version_id: null },
    ]);

    expect(wrapper.text()).toContain('Homepage');
    expect(wrapper.text()).toContain('About');
    expect(wrapper.text()).toContain('Home'); // home badge on the homepage row
    expect(wrapper.text()).toContain('published');
    expect(wrapper.text()).toContain('draft');
  });

  it('shows a distinct "no results" message (not the true empty state) when a search yields nothing', () => {
    const wrapper = mountIndex([], { q: 'nonexistent' });

    expect(wrapper.text()).toContain('No pages match "nonexistent"');
    expect(wrapper.text()).not.toContain('No pages yet');
  });

  it('each row links to its own Builder, not a shared/static url', () => {
    const wrapper = mountIndex([
      samplePage,
      { ...samplePage, id: 42, name: 'About', slug: 'about', is_home: false },
    ]);

    const builderLinks = wrapper.findAll('a').filter((a) => a.text() === 'Open Builder');
    expect(builderLinks).toHaveLength(2);
    expect(builderLinks[0].attributes('href')).toBe('/admin/pages/1/builder');
    expect(builderLinks[1].attributes('href')).toBe('/admin/pages/42/builder');
  });

  it('Preview link is only shown for non-draft pages', () => {
    const wrapper = mountIndex([
      { ...samplePage, id: 1, status: 'published' },
      { ...samplePage, id: 2, status: 'draft' },
    ]);

    const previewLinks = wrapper.findAll('a').filter((a) => a.text() === 'Preview');
    expect(previewLinks).toHaveLength(1);
  });

  it('typing in the search box triggers a debounced server request with the query param', async () => {
    vi.useFakeTimers();
    const wrapper = mountIndex([samplePage]);

    await wrapper.find('input[type="text"]').setValue('spa');

    expect(router.get).not.toHaveBeenCalled(); // debounced, not immediate

    await vi.advanceTimersByTimeAsync(350);

    expect(router.get).toHaveBeenCalledWith(
      '/admin/pages',
      { q: 'spa' },
      expect.objectContaining({ preserveState: true })
    );
    vi.useRealTimers();
  });
});
