import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';

/**
 * Mocked only because it's Inertia's client runtime, not our code:
 * - `router` would otherwise try to perform real navigation.
 * - `Link` needs an active Inertia page context to resolve `href`
 *   normally; a plain <a> stand-in is enough for FacilityCard.vue (used
 *   transitively via FacilityGridSection.vue) to mount and render.
 * Everything else in this test uses the REAL, unmodified store.ts,
 * Canvas.vue, SectionRenderer.vue, registry.ts, and section components.
 */
vi.mock('@inertiajs/vue3', () => ({
  router: { put: vi.fn(), post: vi.fn() },
  Link: {
    props: ['href'],
    render() {
      return h('a', { href: this.href }, this.$slots.default?.());
    },
  },
}));

vi.mock('axios', () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: { facilities: [] } } } })) },
}));

import Canvas from './Canvas.vue';
import draggable from 'vuedraggable';
import { usePageBuilderStore } from './store';

function mountCanvas(store: ReturnType<typeof usePageBuilderStore>) {
  return mount(Canvas, {
    global: {
      provide: { pageBuilderStore: store },
    },
  });
}

describe('Canvas.vue (real component + real store.ts)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders sections from the real Builder store', () => {
    const store = usePageBuilderStore(1, {
      schema_version: 1,
      sections: [
        { id: 'hero-1', type: 'hero', props: { title: 'Welcome' }, settings: {} },
        { id: 'text-1', type: 'text', props: { heading: 'About', body: 'Some text' }, settings: {} },
      ],
    });

    const wrapper = mountCanvas(store);

    expect(wrapper.text()).toContain('Welcome');
    expect(wrapper.text()).toContain('About');
    // Both section-type labels shown in the hover chrome.
    expect(wrapper.findAll('[data-section-type]')).toHaveLength(2);
  });

  it('selecting a section updates selectedSectionId on the real store', async () => {
    const store = usePageBuilderStore(1, {
      schema_version: 1,
      sections: [{ id: 'hero-1', type: 'hero', props: { title: 'Welcome' }, settings: {} }],
    });
    const wrapper = mountCanvas(store);

    expect(store.state.selectedSectionId).toBeNull();

    await wrapper.find('[data-section-id="hero-1"]').trigger('click');

    expect(store.state.selectedSectionId).toBe('hero-1');
  });

  it('the selected section receives the expected visual selection state', async () => {
    const store = usePageBuilderStore(1, {
      schema_version: 1,
      sections: [
        { id: 'hero-1', type: 'hero', props: { title: 'A' }, settings: {} },
        { id: 'text-1', type: 'text', props: { heading: 'B' }, settings: {} },
      ],
    });
    const wrapper = mountCanvas(store);

    // Find the outer selectable wrapper (parent of the [data-section-id] node).
    const heroWrapper = wrapper.find('[data-section-id="hero-1"]').element.parentElement!;
    const textWrapper = wrapper.find('[data-section-id="text-1"]').element.parentElement!;

    expect(heroWrapper.className).not.toContain('border-blue-400');

    store.selectSection('hero-1');
    await wrapper.vm.$nextTick();

    expect(heroWrapper.className).toContain('border-blue-400');
    expect(textWrapper.className).not.toContain('border-blue-400');
  });

  it('the Remove control calls store.removeSection with the correct id', async () => {
    const store = usePageBuilderStore(1, {
      schema_version: 1,
      sections: [{ id: 'hero-1', type: 'hero', props: { title: 'A' }, settings: {} }],
    });
    const removeSpy = vi.spyOn(store, 'removeSection');
    const wrapper = mountCanvas(store);

    const removeButton = wrapper.findAll('button').find((b) => b.text() === 'Remove')!;
    await removeButton.trigger('click');

    expect(removeSpy).toHaveBeenCalledWith('hero-1');
    // And it genuinely reached the real store, not just the spy.
    expect(store.state.sections).toHaveLength(0);
  });

  it('the Duplicate control calls store.duplicateSection with the correct id', async () => {
    const store = usePageBuilderStore(1, {
      schema_version: 1,
      sections: [{ id: 'hero-1', type: 'hero', props: { title: 'A' }, settings: {} }],
    });
    const duplicateSpy = vi.spyOn(store, 'duplicateSection');
    const wrapper = mountCanvas(store);

    const duplicateButton = wrapper.findAll('button').find((b) => b.text() === 'Duplicate')!;
    await duplicateButton.trigger('click');

    expect(duplicateSpy).toHaveBeenCalledWith('hero-1');
    expect(store.state.sections).toHaveLength(2);
    expect(store.state.sections[1].id).not.toBe('hero-1');
  });

  describe('drag/reorder integration boundary', () => {
    /**
     * Honesty note (per instructions): simulating a real SortableJS
     * pointer-drag in jsdom is brittle and not what SortableJS is built
     * to support in a headless DOM - it relies on real browser drag
     * physics/mouse capture. What IS tested here, without faking a
     * successful drag by bypassing Canvas entirely, is the actual
     * integration boundary Canvas.vue owns: the `sections` computed's
     * setter, which is what vuedraggable calls (via v-model) the instant
     * a real drop completes. This exercises Canvas.vue's real wiring -
     * the draggable child component's `update:modelValue` emit is
     * triggered exactly as vuedraggable would on a genuine drop, and we
     * assert it reaches the real store.setSectionsOrder, not a local copy.
     *
     * NOT covered by this test: SortableJS's own pointer-event handling,
     * ghost-element rendering, or drag-start/drag-end DOM mechanics.
     */
    it("a drop event from the draggable child reaches store.setSectionsOrder via Canvas's v-model wiring", async () => {
      const store = usePageBuilderStore(1, {
        schema_version: 1,
        sections: [
          { id: 'a', type: 'hero', props: { title: 'A' }, settings: {} },
          { id: 'b', type: 'text', props: { heading: 'B' }, settings: {} },
        ],
      });
      const setOrderSpy = vi.spyOn(store, 'setSectionsOrder');
      const wrapper = mountCanvas(store);

      const draggableComponent = wrapper.findComponent(draggable);
      expect(draggableComponent.exists()).toBe(true);

      const reordered = [store.state.sections[1], store.state.sections[0]];
      await draggableComponent.vm.$emit('update:modelValue', reordered);
      await wrapper.vm.$nextTick();

      expect(setOrderSpy).toHaveBeenCalledWith(reordered);
      expect(store.state.sections.map((s) => s.id)).toEqual(['b', 'a']);
    });

    it('the draggable child is bound to the real store sections, not a component-local copy', () => {
      const store = usePageBuilderStore(1, {
        schema_version: 1,
        sections: [{ id: 'a', type: 'hero', props: { title: 'A' }, settings: {} }],
      });
      const wrapper = mountCanvas(store);

      const draggableComponent = wrapper.findComponent(draggable);
      // vuedraggable v4 exposes the bound list via the `list`/`modelValue` prop.
      const boundList = draggableComponent.props('modelValue') ?? draggableComponent.props('list');

      expect(boundList).toHaveLength(1);
      expect(boundList[0].id).toBe('a');
    });
  });

  it('does not create a second reactive copy of sections - external store mutations are reflected without re-mounting', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    const wrapper = mountCanvas(store);

    expect(wrapper.text()).toContain('No sections yet');

    // Mutate the store directly (as e.g. ComponentLibrary would), NOT
    // through any Canvas-owned copy.
    store.addSection('hero');
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).not.toContain('No sections yet');
    expect(wrapper.findAll('[data-section-type]')).toHaveLength(1);
  });
});
