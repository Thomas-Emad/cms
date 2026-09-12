import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({ router: { put: vi.fn(), post: vi.fn() } }));
vi.mock('axios', () => ({ default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: {} } } })) } }));

import ComponentLibrary from './ComponentLibrary.vue';
import { SECTION_REGISTRY } from './registry';
import { usePageBuilderStore } from './store';

function mountLibrary(store: ReturnType<typeof usePageBuilderStore>) {
  return mount(ComponentLibrary, { global: { provide: { pageBuilderStore: store } } });
}

describe('ComponentLibrary.vue exposes and adds all 12 real section types', () => {
  beforeEach(() => vi.clearAllMocks());

  it('renders exactly 12 buttons, one per registry entry', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    expect(wrapper.findAll('button')).toHaveLength(12);
  });

  it.each(Object.keys(SECTION_REGISTRY))('clicking "%s" adds it through the real store with valid default state', async (type) => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);

    const button = wrapper.findAll('button').find((b) => b.text().includes(SECTION_REGISTRY[type].label))!;
    await button.trigger('click');

    expect(store.state.sections).toHaveLength(1);
    const added = store.state.sections[0];

    expect(added.type).toBe(type);
    expect(typeof added.id).toBe('string');
    expect(added.id.length).toBeGreaterThan(0);
    expect(added.props).toEqual(SECTION_REGISTRY[type].defaultProps);
    expect(added.settings).toEqual(SECTION_REGISTRY[type].defaultSettings);
  });

  it('adding all 12 types in sequence produces 12 sections with 12 unique ids', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountLibrary(store);
    const buttons = wrapper.findAll('button');

    for (const button of buttons) {
      await button.trigger('click');
    }

    expect(store.state.sections).toHaveLength(12);
    const ids = store.state.sections.map((s) => s.id);
    expect(new Set(ids).size).toBe(12);

    const types = store.state.sections.map((s) => s.type).sort();
    expect(types).toEqual(Object.keys(SECTION_REGISTRY).sort());
  });
});
