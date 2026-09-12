import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
  router: { put: vi.fn(), post: vi.fn() },
}));

vi.mock('axios', () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: { facilities: [] } } } })) },
}));

import ComponentLibrary from './ComponentLibrary.vue';
import { SECTION_REGISTRY } from './registry';
import { usePageBuilderStore } from './store';

function mountLibrary(store: ReturnType<typeof usePageBuilderStore>) {
  return mount(ComponentLibrary, {
    global: { provide: { pageBuilderStore: store } },
  });
}

describe('ComponentLibrary.vue (real component + real store.ts + real registry.ts)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('available section types come from the real SECTION_REGISTRY, not a hardcoded list', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    const buttons = wrapper.findAll('button');
    const registryKeys = Object.keys(SECTION_REGISTRY);

    expect(buttons).toHaveLength(registryKeys.length);
    for (const key of registryKeys) {
      expect(wrapper.text()).toContain(SECTION_REGISTRY[key].label);
    }
  });

  it('clicking a section type adds it through the real store', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    expect(store.state.sections).toHaveLength(0);

    const heroButton = wrapper.findAll('button').find((b) => b.text().includes(SECTION_REGISTRY.hero.label))!;
    await heroButton.trigger('click');

    expect(store.state.sections).toHaveLength(1);
    expect(store.state.sections[0].type).toBe('hero');
  });

  it('newly added sections receive unique ids across multiple clicks', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    const textButton = wrapper.findAll('button').find((b) => b.text().includes(SECTION_REGISTRY.text.label))!;
    await textButton.trigger('click');
    await textButton.trigger('click');

    expect(store.state.sections).toHaveLength(2);
    expect(store.state.sections[0].id).not.toBe(store.state.sections[1].id);
  });

  it("uses the registry's defaultProps and empty settings for a newly added section", async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    const facilityGridButton = wrapper.findAll('button')
      .find((b) => b.text().includes(SECTION_REGISTRY['facility-grid'].label))!;
    await facilityGridButton.trigger('click');

    const added = store.state.sections[0];
    expect(added.props).toEqual(SECTION_REGISTRY['facility-grid'].defaultProps);
    expect(added.settings).toEqual({});
  });

  it('clicking different section types in sequence adds each with its own correct type and defaults', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    const buttons = wrapper.findAll('button');
    await buttons.find((b) => b.text().includes(SECTION_REGISTRY.hero.label))!.trigger('click');
    await buttons.find((b) => b.text().includes(SECTION_REGISTRY.text.label))!.trigger('click');

    expect(store.state.sections.map((s) => s.type)).toEqual(['hero', 'text']);
    expect(store.state.sections[0].props).toEqual(SECTION_REGISTRY.hero.defaultProps);
    expect(store.state.sections[1].props).toEqual(SECTION_REGISTRY.text.defaultProps);
  });
});
