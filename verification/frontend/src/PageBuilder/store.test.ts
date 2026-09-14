import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({ router: { put: vi.fn(), post: vi.fn() } }));
vi.mock('axios', () => ({ default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: {} } } })) } }));
vi.mock('./registry', () => ({
  SECTION_REGISTRY: {
    hero: { defaultProps: { title: '' }, defaultSettings: {}, isDynamic: false },
    'restaurant-hero': { defaultProps: { subtitle_override: '' }, defaultSettings: {}, isDynamic: true },
  },
}));

import { usePageBuilderStore } from './store';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

describe('usePageBuilderStore — generalized apiBase (real store.ts)', () => {
  beforeEach(() => vi.clearAllMocks());

  it('a Page-shaped apiBase posts drafts/publishes to the Page URLs', () => {
    const store = usePageBuilderStore({ apiBase: '/admin/pages/42' }, { schema_version: 1, sections: [] });

    store.saveDraft();
    expect(router.put).toHaveBeenCalledWith('/admin/pages/42/draft', expect.anything(), expect.anything());

    store.publish();
    expect(router.post).toHaveBeenCalledWith('/admin/pages/42/publish', {}, expect.anything());
  });

  it('a Restaurant-presentation-shaped apiBase posts to the RESTAURANT URLs - same store, zero duplicated logic', () => {
    const store = usePageBuilderStore(
      { apiBase: '/admin/restaurants/7/presentation' },
      { schema_version: 1, sections: [] }
    );

    store.saveDraft();
    expect(router.put).toHaveBeenCalledWith('/admin/restaurants/7/presentation/draft', expect.anything(), expect.anything());

    store.publish();
    expect(router.post).toHaveBeenCalledWith('/admin/restaurants/7/presentation/publish', {}, expect.anything());
  });

  it('live preview resolution posts to the correct resolve-preview URL for a restaurant context', async () => {
    vi.useFakeTimers();
    const store = usePageBuilderStore(
      { apiBase: '/admin/restaurants/7/presentation' },
      { schema_version: 1, sections: [] }
    );

    store.addSection('restaurant-hero');
    await vi.advanceTimersByTimeAsync(500);

    expect(axios.post).toHaveBeenCalledWith(
      '/admin/restaurants/7/presentation/resolve-preview',
      expect.anything()
    );
    vi.useRealTimers();
  });
});
