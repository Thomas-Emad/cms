import { describe, it, expect, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({ router: { put: vi.fn(), post: vi.fn() } }));
vi.mock('axios', () => ({ default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: {} } } })) } }));

import { SECTION_REGISTRY } from './registry';
import sectionTypesManifest from './section-types.json';

const EXPECTED_TYPES = [
  'hero', 'text', 'image', 'gallery',
  'facility-grid', 'restaurant-grid', 'service-grid',
  'events', 'offers', 'experiences',
  'cta', 'spacer',
];

describe('SECTION_REGISTRY (real registry.ts)', () => {
  it('has exactly 12 entries', () => {
    expect(Object.keys(SECTION_REGISTRY)).toHaveLength(12);
  });

  it.each(EXPECTED_TYPES)('registers "%s"', (type) => {
    expect(SECTION_REGISTRY[type]).toBeDefined();
  });

  it.each(Object.entries(SECTION_REGISTRY))('"%s" entry has the complete required shape', (type, entry) => {
    expect(entry.component).toBeDefined();
    expect(typeof entry.label).toBe('string');
    expect(entry.label.length).toBeGreaterThan(0);
    expect(typeof entry.icon).toBe('string');
    expect(typeof entry.defaultProps).toBe('object');
    expect(typeof entry.defaultSettings).toBe('object');
    expect(typeof entry.isDynamic).toBe('boolean');
    expect(Array.isArray(entry.editorFields)).toBe(true);
  });

  it.each(Object.entries(SECTION_REGISTRY))('"%s" has at least one editable field UNLESS it genuinely has no props (spacer aside)', (type, entry) => {
    // Every section except a truly prop-less one should expose something
    // editable - this guards against silently shipping a section with
    // real props but zero editorFields (unreachable in the Builder UI).
    const propKeys = Object.keys(entry.defaultProps);
    if (propKeys.length > 0) {
      expect(entry.editorFields.length).toBeGreaterThan(0);
    }
  });

  it('the 6 spec-designated dynamic sections are marked isDynamic, the other 6 are not', () => {
    const dynamicTypes = ['facility-grid', 'restaurant-grid', 'service-grid', 'events', 'offers', 'experiences'];
    const staticTypes = ['hero', 'text', 'image', 'gallery', 'cta', 'spacer'];

    for (const type of dynamicTypes) {
      expect(SECTION_REGISTRY[type].isDynamic, `${type} should be isDynamic`).toBe(true);
    }
    for (const type of staticTypes) {
      expect(SECTION_REGISTRY[type].isDynamic, `${type} should NOT be isDynamic`).toBe(false);
    }
  });

  describe('registry parity with the canonical manifest', () => {
    it('Object.keys(SECTION_REGISTRY) exactly matches section-types.json', () => {
      const registryKeys = Object.keys(SECTION_REGISTRY).slice().sort();
      const manifestKeys = [...(sectionTypesManifest as string[])].sort();

      expect(registryKeys).toEqual(manifestKeys);
    });
  });
});
