import type { Component } from 'vue';
import HeroSection from './sections/HeroSection.vue';
import TextSection from './sections/TextSection.vue';
import FacilityGridSection from './sections/FacilityGridSection.vue';

export interface EditorField {
  key: string;
  type: 'text' | 'textarea' | 'number' | 'toggle' | 'select' | 'media' | 'url';
  label: string;
  options?: (string | number)[];
  min?: number;
  max?: number;
}

export interface SectionRegistryEntry {
  component: Component;
  label: string;
  defaultProps: Record<string, unknown>;
  /**
   * True for sections whose data comes from a database query
   * (facility-grid, ...). Drives whether a prop change triggers a
   * debounced call to the live preview-resolve endpoint, or just
   * re-renders instantly client-side (static sections). Mirrors
   * SectionDefinition::isDynamic() on the backend - kept in sync by
   * convention, the same way the two registries' type keys are.
   */
  isDynamic: boolean;
  editorFields: EditorField[];
}

/**
 * Adding a new section type here = one new component + one entry.
 * SectionRenderer.vue never branches on section.type by name - it always
 * looks up this registry. Keep the keys identical to
 * App\Services\PageBuilder\SectionRegistry's keys - that correspondence is
 * what lets a section saved via one side render correctly via the other.
 */
export const SECTION_REGISTRY: Record<string, SectionRegistryEntry> = {
  hero: {
    component: HeroSection,
    label: 'Hero',
    defaultProps: { title: '', subtitle: '', media_id: null, button_text: '', button_url: '' },
    isDynamic: false,
    editorFields: [
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'subtitle', type: 'text', label: 'Subtitle' },
      { key: 'media_id', type: 'media', label: 'Background image' },
      { key: 'button_text', type: 'text', label: 'Button label' },
      { key: 'button_url', type: 'url', label: 'Button link' },
    ],
  },
  text: {
    component: TextSection,
    label: 'Text',
    defaultProps: { heading: '', body: '' },
    isDynamic: false,
    editorFields: [
      { key: 'heading', type: 'text', label: 'Heading' },
      { key: 'body', type: 'textarea', label: 'Body text' },
    ],
  },
  'facility-grid': {
    component: FacilityGridSection,
    label: 'Facility Grid',
    defaultProps: { title: 'Explore Our Facilities', description: null, category: null, featured_only: false, limit: 6, columns: 3 },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      {
        key: 'category', type: 'select', label: 'Category',
        options: ['wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other'],
      },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
      { key: 'columns', type: 'select', label: 'Columns', options: [2, 3, 4] },
    ],
  },
  // Remaining 9 section types land next checkpoint.
};
