import { describe, it, expect } from 'vitest';
import { SECTION_REGISTRY, sectionsForContext } from './registry';

describe('registry.ts context filtering (real registry, real components)', () => {
  it('the "page" context includes page-only and any-context sections, but NO restaurant-only sections', () => {
    const types = sectionsForContext('page').map(([type]) => type);

    expect(types).toContain('hero');
    expect(types).toContain('facility-grid');
    expect(types).toContain('cta'); // 'any'
    expect(types).toContain('spacer'); // 'any'
    expect(types).not.toContain('restaurant-hero');
    expect(types).not.toContain('restaurant-menu');
  });

  it('the "restaurant" context includes restaurant-only and any-context sections, but NO page-only sections', () => {
    const types = sectionsForContext('restaurant').map(([type]) => type);

    expect(types).toContain('restaurant-hero');
    expect(types).toContain('restaurant-menu');
    expect(types).toContain('restaurant-info');
    expect(types).toContain('restaurant-gallery');
    expect(types).toContain('restaurant-location');
    expect(types).toContain('cta'); // 'any' still shows up
    expect(types).toContain('text'); // 'any'
    expect(types).not.toContain('hero'); // page-only
    expect(types).not.toContain('facility-grid'); // page-only
  });

  it('every registry entry declares a non-empty contexts array', () => {
    for (const [type, entry] of Object.entries(SECTION_REGISTRY)) {
      expect(entry.contexts.length, `${type} has no contexts declared`).toBeGreaterThan(0);
    }
  });

  it('all 5 restaurant-* entity sections exist with real components', () => {
    for (const type of ['restaurant-hero', 'restaurant-info', 'restaurant-gallery', 'restaurant-menu', 'restaurant-location']) {
      expect(SECTION_REGISTRY[type]).toBeDefined();
      expect(SECTION_REGISTRY[type].component).toBeDefined();
      expect(SECTION_REGISTRY[type].contexts).toEqual(['restaurant']);
    }
  });

  it('registry now has 17 total types', () => {
    expect(Object.keys(SECTION_REGISTRY)).toHaveLength(17);
  });
});
