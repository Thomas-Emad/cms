import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
  router: { put: vi.fn(), post: vi.fn() },
  Link: {
    props: ['href'],
    render() {
      return h('a', { href: this.href }, this.$slots.default?.());
    },
  },
}));
vi.mock('axios', () => ({ default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: {} } } })) } }));

import SectionRenderer from './SectionRenderer.vue';
import { SECTION_REGISTRY } from './registry';
import type { Section } from '@/types/pageBuilder';

const SAMPLE_DATA: Record<string, unknown> = {
  'facility-grid': { facilities: [{ id: 1, name: 'Spa', slug: 'spa', category: 'wellness' }] },
  'restaurant-grid': { restaurants: [{ id: 1, name: 'Azure', slug: 'azure', cuisine: 'Mediterranean' }] },
  'service-grid': { services: [{ id: 1, name: 'Room Service', slug: 'room-service', request_enabled: true }] },
  events: { events: [{ id: 1, title: 'Live Music', slug: 'live-music', start_date: '2026-01-01' }] },
  offers: { offers: [{ id: 1, title: 'Spa Package', slug: 'spa-package' }] },
  experiences: { experiences: [{ id: 1, title: 'Yoga', slug: 'yoga' }] },
  image: { media: { url: 'https://example.com/a.jpg', alt_text: 'A photo' } },
  gallery: { media: [{ url: 'https://example.com/a.jpg', alt_text: null }] },
};

describe('SectionRenderer.vue handles all 12 real section types', () => {
  it.each(Object.entries(SECTION_REGISTRY))('renders "%s" with its registry defaultProps/defaultSettings without throwing', (type, entry) => {
    const section: Section = {
      id: `${type}-1`,
      type,
      props: entry.defaultProps,
      settings: entry.defaultSettings,
      data: SAMPLE_DATA[type] as never,
    };

    expect(() => {
      const wrapper = mount(SectionRenderer, {
        props: { section, mode: 'edit' },
      });
      expect(wrapper.html().length).toBeGreaterThan(0);
    }).not.toThrow();
  });

  it('shows an explicit "unknown section type" placeholder in edit mode for an unregistered type, rather than silently rendering nothing', () => {
    const wrapper = mount(SectionRenderer, {
      props: {
        section: { id: 'x', type: 'not-a-real-type', props: {}, settings: {} },
        mode: 'edit',
      },
    });

    expect(wrapper.text()).toContain('Unknown section type');
  });

  it('the dynamic sections correctly display their resolved data, not just placeholder chrome', () => {
    const facilityGridSection: Section = {
      id: 'fg-1',
      type: 'facility-grid',
      props: SECTION_REGISTRY['facility-grid'].defaultProps,
      settings: {},
      data: { facilities: [{ id: 1, name: 'Serenity Spa', slug: 'serenity-spa', category: 'wellness' }] } as never,
    };

    const wrapper = mount(SectionRenderer, { props: { section: facilityGridSection, mode: 'live' } });

    expect(wrapper.text()).toContain('Serenity Spa');
  });
});
