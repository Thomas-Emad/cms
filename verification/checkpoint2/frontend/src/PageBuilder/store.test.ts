import { describe, it, expect, vi, beforeEach } from 'vitest';

/**
 * These mocks replace store.ts's collaborators at their IO boundary:
 *   - '@inertiajs/vue3' router.put/post would otherwise try to perform a
 *     real browser navigation/XHR, which doesn't exist in this Node test
 *     environment and isn't what we're verifying anyway - we want to
 *     assert WHAT payload the store sends, not exercise Inertia's actual
 *     network client.
 *   - 'axios' is mocked the same way for the live preview-resolve call.
 *   - './registry' is mocked with values copied verbatim from the real
 *     resources/js/PageBuilder/registry.ts, because the real file imports
 *     .vue Single File Components for their `component` field, which
 *     requires a Vue SFC compiler this lightweight verification setup
 *     doesn't include. The logic under test (store.ts) is the real,
 *     unmodified production file - only this one data-shaped collaborator
 *     is stubbed.
 *
 * Everything else - generateSectionId, addSection, removeSection,
 * updateSectionProps, updateSectionSettings, the debounce, and the save
 * payload shape - is the actual production store.ts logic, unmodified.
 */
vi.mock('@inertiajs/vue3', () => ({
  router: { put: vi.fn(), post: vi.fn() },
}));

vi.mock('axios', () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: { facilities: [] } } } })) },
}));

vi.mock('./registry', () => ({
  SECTION_REGISTRY: {
    hero: {
      label: 'Hero',
      defaultProps: { title: '', subtitle: '', media_id: null, button_text: '', button_url: '' },
      isDynamic: false,
      editorFields: [],
    },
    text: {
      label: 'Text',
      defaultProps: { heading: '', body: '' },
      isDynamic: false,
      editorFields: [],
    },
    'facility-grid': {
      label: 'Facility Grid',
      defaultProps: { title: 'Explore Our Facilities', description: null, category: null, featured_only: false, limit: 6, columns: 3 },
      isDynamic: true,
      editorFields: [],
    },
  },
}));

import { usePageBuilderStore } from './store';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

describe('usePageBuilderStore (real store.ts logic)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.useFakeTimers();
  });

  it('starts with the initial sections and no selection', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    expect(store.state.sections).toEqual([]);
    expect(store.state.selectedSectionId).toBeNull();
    expect(store.state.isDirty).toBe(false);
  });

  it('addSection uses the registry defaultProps and generates a stable unique id', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });

    store.addSection('hero');

    expect(store.state.sections).toHaveLength(1);
    const section = store.state.sections[0];
    expect(section.type).toBe('hero');
    expect(section.props).toEqual({ title: '', subtitle: '', media_id: null, button_text: '', button_url: '' });
    expect(section.id).toMatch(/^hero-/);
    expect(store.state.selectedSectionId).toBe(section.id);
    expect(store.state.isDirty).toBe(true);
  });

  it('adding two sections of the same type produces two DIFFERENT ids', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('text');
    store.addSection('text');

    const [a, b] = store.state.sections;
    expect(a.id).not.toBe(b.id);
  });

  it('removeSection removes only the targeted section and clears selection if it was selected', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    store.addSection('text');
    const [hero, text] = store.state.sections;

    store.selectSection(hero.id);
    store.removeSection(hero.id);

    expect(store.state.sections.map((s) => s.id)).toEqual([text.id]);
    expect(store.state.selectedSectionId).toBeNull();
  });

  it('updateSectionProps merges into existing props without clobbering untouched keys', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    const id = store.state.sections[0].id;

    store.updateSectionProps(id, { title: 'Welcome' });
    store.updateSectionProps(id, { subtitle: 'Discover more' });

    expect(store.state.sections[0].props).toMatchObject({
      title: 'Welcome',
      subtitle: 'Discover more',
    });
  });

  it('editing a dynamic section prop (facility-grid) triggers a debounced live-preview resolve call, not a frontend query', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('facility-grid');
    const id = store.state.sections[0].id;
    vi.clearAllMocks(); // clear the initial addSection resolve call

    store.updateSectionProps(id, { category: 'wellness' });

    // Not called yet - it's debounced.
    expect(axios.post).not.toHaveBeenCalled();

    await vi.advanceTimersByTimeAsync(500);

    expect(axios.post).toHaveBeenCalledTimes(1);
    const [url, body] = (axios.post as any).mock.calls[0];
    expect(url).toBe('/admin/pages/1/resolve-preview');
    expect(body.sections[0].props.category).toBe('wellness');
  });

  it('rapid successive prop edits only trigger ONE resolve call (debounce works), reflecting the LATEST props', async () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('facility-grid');
    const id = store.state.sections[0].id;
    vi.clearAllMocks();

    store.updateSectionProps(id, { category: 'wellness' });
    store.updateSectionProps(id, { category: 'fitness' });
    store.updateSectionProps(id, { category: 'pool' });

    await vi.advanceTimersByTimeAsync(500);

    expect(axios.post).toHaveBeenCalledTimes(1);
    const [, body] = (axios.post as any).mock.calls[0];
    // The LAST edit wins - proving the debounce doesn't send stale
    // intermediate props, matching the "must not depend on stale resolved
    // data" requirement from checkpoint 1's design.
    expect(body.sections[0].props.category).toBe('pool');
  });

  it('editing a static section (hero) never triggers a resolve-preview call', () => {
    const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
    store.addSection('hero');
    const id = store.state.sections[0].id;
    vi.clearAllMocks();

    store.updateSectionProps(id, { title: 'Something new' });

    expect(axios.post).not.toHaveBeenCalled();
  });

  it('saveDraft sends a flat sections array (id/type/props/settings only, no schema_version wrapper, no resolved data)', () => {
    const store = usePageBuilderStore(42, {
      schema_version: 1,
      sections: [{ id: 'hero-1', type: 'hero', props: { title: 'Hi' }, settings: {}, data: { shouldNotBeSent: true } as any }],
    });

    store.saveDraft();

    expect(router.put).toHaveBeenCalledTimes(1);
    const [url, payload] = (router.put as any).mock.calls[0];
    expect(url).toBe('/admin/pages/42/draft');
    expect(payload).toEqual({
      sections: [{ id: 'hero-1', type: 'hero', props: { title: 'Hi' }, settings: {} }],
    });
    // Explicitly NOT present: resolved `data`, and no top-level
    // schema_version - matching "do not store resolved dynamic content in
    // the page JSON" and "SaveDraftAction is the schema_version authority".
    expect(payload.sections[0].data).toBeUndefined();
    expect((payload as any).schema_version).toBeUndefined();
  });

  it('publish posts to the publish endpoint for the correct page id', () => {
    const store = usePageBuilderStore(7, { schema_version: 1, sections: [] });
    store.publish();

    expect(router.post).toHaveBeenCalledWith('/admin/pages/7/publish', {}, expect.any(Object));
  });
});
