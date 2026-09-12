import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';

/**
 * Partial mock: Link and router are stubbed (external navigation/HTTP
 * concerns), but useForm is the REAL implementation from the actual
 * installed @inertiajs/vue3 package via importActual - this test
 * exercises Create.vue's real interaction with Inertia's real form
 * helper (reactive fields, errors, processing state), not a hand-rolled
 * stand-in for it.
 */
vi.mock('@inertiajs/vue3', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@inertiajs/vue3')>();
  return {
    ...actual,
    Link: {
      props: ['href'],
      render() {
        return h('a', { href: this.href }, this.$slots.default?.());
      },
    },
  };
});

import Create from './Create.vue';

describe('Admin/Pages/Create.vue (real component, real useForm)', () => {
  beforeEach(() => vi.clearAllMocks());

  it('renders Page Name, Slug, and Home Page toggle fields', () => {
    const wrapper = mount(Create);

    expect(wrapper.find('input[type="text"]').exists()).toBe(true);
    expect(wrapper.find('input[type="checkbox"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Page Name');
    expect(wrapper.text()).toContain('Set as home page');
  });

  it('auto-slugifies the name into the slug field until the slug is manually edited', async () => {
    const wrapper = mount(Create);

    const nameInput = wrapper.find('input[placeholder="e.g. About Us"]');
    await nameInput.setValue('About Our Hotel!');

    const slugInput = wrapper.find('input[placeholder="about-us"]');
    expect((slugInput.element as HTMLInputElement).value).toBe('about-our-hotel');
  });

  it('manually editing the slug stops further auto-slugify updates from the name field', async () => {
    const wrapper = mount(Create);

    const nameInput = wrapper.find('input[placeholder="e.g. About Us"]');
    const slugInput = wrapper.find('input[placeholder="about-us"]');

    await nameInput.setValue('About Us');
    await slugInput.setValue('custom-slug');
    await nameInput.setValue('About Us Changed');

    expect((slugInput.element as HTMLInputElement).value).toBe('custom-slug');
  });

  it('the slug field is hidden entirely when "Set as home page" is checked', async () => {
    const wrapper = mount(Create);

    expect(wrapper.find('input[placeholder="about-us"]').exists()).toBe(true);

    await wrapper.find('input[type="checkbox"]').setValue(true);

    expect(wrapper.find('input[placeholder="about-us"]').exists()).toBe(false);
  });

  it('displays validation errors from a failed submission (real useForm error state)', async () => {
    const wrapper = mount(Create);
    // Simulate what Inertia would populate after a 422 response - real
    // useForm exposes `.errors` reactively, this is what Create.vue reads.
    (wrapper.vm as any).form.errors.name = 'The name field is required.';
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('The name field is required.');
  });

  it('the Cancel link points back to the Pages index', () => {
    const wrapper = mount(Create);

    const cancelLink = wrapper.findAll('a').find((a) => a.text() === 'Cancel');
    expect(cancelLink?.attributes('href')).toBe('/admin/pages');
  });

  it('submit button shows a processing state and is disabled while submitting', async () => {
    const wrapper = mount(Create);
    (wrapper.vm as any).form.processing = true;
    await wrapper.vm.$nextTick();

    const submitButton = wrapper.find('button[type="submit"]');
    expect(submitButton.text()).toContain('Creating');
    expect((submitButton.element as HTMLButtonElement).disabled).toBe(true);
  });
});
