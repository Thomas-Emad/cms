import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
  router: { put: vi.fn(), post: vi.fn() },
}));

vi.mock('axios', () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: { facilities: [] } } } })) },
}));

import SettingsPanel from './SettingsPanel.vue';
import { usePageBuilderStore } from './store';

function mountPanel(store: ReturnType<typeof usePageBuilderStore>) {
  return mount(SettingsPanel, {
    global: { provide: { pageBuilderStore: store } },
  });
}

describe('SettingsPanel.vue (real component + real store.ts)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.useFakeTimers();
  });

  it('shows a placeholder when no section is selected', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountPanel(store);

    expect(wrapper.text()).toContain('Select a section to edit its properties.');
  });

  it('reads the currently selected section from the real store', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    store.updateSectionProps(store.state.sections[0].id, { title: 'Welcome to Grand Horizon' });

    const wrapper = mountPanel(store);

    expect(wrapper.text()).toContain('Hero'); // entry.label
    const titleInput = wrapper.find('input[type="text"]');
    expect((titleInput.element as HTMLInputElement).value).toBe('Welcome to Grand Horizon');
  });

  it('editing a text field uses the BATCHED update path, not the immediate one', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    const id = store.state.sections[0].id;

    const batchedSpy = vi.spyOn(store, 'updateSectionPropsBatched');
    const immediateSpy = vi.spyOn(store, 'updateSectionProps');
    const wrapper = mountPanel(store);

    const titleInput = wrapper.find('input[type="text"]');
    await titleInput.setValue('New Title');

    expect(batchedSpy).toHaveBeenCalledWith(id, { title: 'New Title' }, `${id}:title`);
    expect(immediateSpy).not.toHaveBeenCalled();
  });

  it('editing an immediate field (toggle) uses the IMMEDIATE update path, not the batched one', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('facility-grid');
    const id = store.state.sections[0].id;

    const batchedSpy = vi.spyOn(store, 'updateSectionPropsBatched');
    const immediateSpy = vi.spyOn(store, 'updateSectionProps');
    const wrapper = mountPanel(store);

    const toggle = wrapper.find('input[type="checkbox"]');
    await toggle.setValue(true);

    expect(immediateSpy).toHaveBeenCalledWith(id, { featured_only: true });
    expect(batchedSpy).not.toHaveBeenCalled();
  });

  it('editing an immediate field (select) uses the IMMEDIATE update path', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('facility-grid');
    const id = store.state.sections[0].id;

    const immediateSpy = vi.spyOn(store, 'updateSectionProps');
    const wrapper = mountPanel(store);

    const select = wrapper.find('select');
    await select.setValue('wellness');

    expect(immediateSpy).toHaveBeenCalledWith(id, { category: 'wellness' });
  });

  it('changes made in the panel actually reach the real store state (not just the spy)', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    const id = store.state.sections[0].id;
    const wrapper = mountPanel(store);

    await wrapper.find('input[type="text"]').setValue('Reached The Store');

    expect(store.state.sections[0].props.title).toBe('Reached The Store');
  });

  it('changing the selected section updates the displayed settings', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    store.updateSectionProps(store.state.sections[0].id, { title: 'First Section' });
    store.addSection('text');
    store.updateSectionProps(store.state.sections[1].id, { heading: 'Second Section' });

    store.selectSection(store.state.sections[0].id);
    const wrapper = mountPanel(store);
    expect(wrapper.find('input[type="text"]').element.getAttribute('value') ?? (wrapper.find('input[type="text"]').element as HTMLInputElement).value).toBe('First Section');

    store.selectSection(store.state.sections[1].id);
    await wrapper.vm.$nextTick();

    // Text section's heading field is a text input.
    expect((wrapper.find('input[type="text"]').element as HTMLInputElement).value).toBe('Second Section');
    expect(wrapper.text()).toContain('Text'); // now showing the Text entry's label, not Hero's
  });

  it('handles a missing/unknown selectedSectionId safely (no crash, falls back to placeholder)', async () => {
    const store = usePageBuilderStore(1, {
      schema_version: 1,
      sections: [{ id: 'hero-1', type: 'hero', props: { title: 'A' }, settings: {} }],
    });
    const wrapper = mountPanel(store);

    expect(() => store.selectSection('does-not-exist')).not.toThrow();
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Select a section to edit its properties.');
  });

  it('rapid typing into the same field still results in only ONE history entry (integration confirmation of checkpoint 3 batching, exercised through the real component)', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    const wrapper = mountPanel(store);
    const before = store.history.past.length;

    const input = wrapper.find('input[type="text"]');
    await input.setValue('H');
    await vi.advanceTimersByTimeAsync(50);
    await input.setValue('He');
    await vi.advanceTimersByTimeAsync(50);
    await input.setValue('Hello');

    expect(store.history.past.length).toBe(before + 1);
    expect(store.state.sections[0].props.title).toBe('Hello');
  });
});
