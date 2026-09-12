import type { Component } from 'vue';
import HeroSection from './sections/HeroSection.vue';
import TextSection from './sections/TextSection.vue';
import ImageSection from './sections/ImageSection.vue';
import GallerySection from './sections/GallerySection.vue';
import FacilityGridSection from './sections/FacilityGridSection.vue';
import RestaurantGridSection from './sections/RestaurantGridSection.vue';
import ServiceGridSection from './sections/ServiceGridSection.vue';
import EventsSection from './sections/EventsSection.vue';
import OffersSection from './sections/OffersSection.vue';
import ExperiencesSection from './sections/ExperiencesSection.vue';
import CtaSection from './sections/CtaSection.vue';
import SpacerSection from './sections/SpacerSection.vue';

export interface EditorField {
  key: string;
  type: 'text' | 'textarea' | 'number' | 'toggle' | 'select' | 'media' | 'media-list' | 'url';
  label: string;
  options?: (string | number)[];
  min?: number;
  max?: number;
}

export interface SectionRegistryEntry {
  component: Component;
  label: string;
  /**
   * Emoji/text icon shown in the Component Library - intentionally not a
   * real icon library dependency for this checkpoint (production polish
   * is explicitly out of scope). Swap for lucide-react/heroicons-vue
   * later without touching anything that reads this field's TYPE, only
   * its rendering in ComponentLibrary.vue.
   */
  icon: string;
  defaultProps: Record<string, unknown>;
  defaultSettings: Record<string, unknown>;
  isDynamic: boolean;
  editorFields: EditorField[];
}

const FACILITY_CATEGORIES = ['wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other'];

/**
 * All 12 section types from the Phase 3 design doc's initial list. Every
 * entry's shape is identical (component/label/icon/defaultProps/
 * defaultSettings/isDynamic/editorFields) - SectionRenderer, Canvas,
 * SettingsPanel, and ComponentLibrary never branch on `type` by name;
 * they only ever read through this shape. Keep this file's keys in sync
 * with the backend SectionRegistry's keys AND with section-types.json
 * (see SectionRegistryParityTest / registry.test.ts).
 */
export const SECTION_REGISTRY: Record<string, SectionRegistryEntry> = {
  hero: {
    component: HeroSection,
    label: 'Hero',
    icon: '🖼️',
    defaultProps: { title: '', subtitle: '', media_id: null, button_text: '', button_url: '' },
    defaultSettings: { background: 'transparent', padding: 'large' },
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
    icon: '📝',
    defaultProps: { heading: '', body: '' },
    defaultSettings: { padding: 'medium' },
    isDynamic: false,
    editorFields: [
      { key: 'heading', type: 'text', label: 'Heading' },
      { key: 'body', type: 'textarea', label: 'Body text' },
    ],
  },
  image: {
    component: ImageSection,
    label: 'Image',
    icon: '🖼️',
    defaultProps: { media_id: null, caption: '', link_url: '' },
    defaultSettings: { padding: 'medium' },
    isDynamic: false,
    editorFields: [
      { key: 'media_id', type: 'media', label: 'Image' },
      { key: 'caption', type: 'text', label: 'Caption' },
      { key: 'link_url', type: 'url', label: 'Link (optional)' },
    ],
  },
  gallery: {
    component: GallerySection,
    label: 'Gallery',
    icon: '🎞️',
    defaultProps: { title: '', media_ids: [] },
    defaultSettings: { padding: 'medium', layout: 'grid' },
    isDynamic: false,
    editorFields: [
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'media_ids', type: 'media-list', label: 'Images' },
    ],
  },
  'facility-grid': {
    component: FacilityGridSection,
    label: 'Facility Grid',
    icon: '🏨',
    defaultProps: { title: 'Explore Our Facilities', description: null, category: null, featured_only: false, limit: 6, columns: 3 },
    defaultSettings: { background: 'light', padding: 'medium' },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      { key: 'category', type: 'select', label: 'Category', options: FACILITY_CATEGORIES },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
      { key: 'columns', type: 'select', label: 'Columns', options: [2, 3, 4] },
    ],
  },
  'restaurant-grid': {
    component: RestaurantGridSection,
    label: 'Restaurant Grid',
    icon: '🍽️',
    defaultProps: { title: 'Dining at Grand Horizon', description: null, cuisine: null, featured_only: false, limit: 6 },
    defaultSettings: { background: 'light', padding: 'medium' },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      { key: 'cuisine', type: 'text', label: 'Cuisine filter (optional)' },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  'service-grid': {
    component: ServiceGridSection,
    label: 'Service Grid',
    icon: '🛎️',
    defaultProps: { title: 'Hotel Services', limit: 6 },
    defaultSettings: { padding: 'medium' },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  events: {
    component: EventsSection,
    label: 'Events',
    icon: '📅',
    defaultProps: { title: 'Upcoming Events', limit: 4, upcoming_only: true },
    defaultSettings: { padding: 'medium' },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'upcoming_only', type: 'toggle', label: 'Upcoming only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  offers: {
    component: OffersSection,
    label: 'Offers',
    icon: '🏷️',
    defaultProps: { title: 'Special Offers', limit: 4, active_only: true, featured_only: false },
    defaultSettings: { padding: 'medium' },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'active_only', type: 'toggle', label: 'Active only (hide expired)' },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  experiences: {
    component: ExperiencesSection,
    label: 'Experiences',
    icon: '🧘',
    defaultProps: { title: 'Experiences', description: null, category: null, featured_only: false, limit: 4 },
    defaultSettings: { background: 'light', padding: 'medium' },
    isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      { key: 'category', type: 'text', label: 'Category filter (optional)' },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  cta: {
    component: CtaSection,
    label: 'Call to Action',
    icon: '📣',
    defaultProps: { heading: '', subheading: '', button_text: '', button_url: '' },
    defaultSettings: { background: 'brand', padding: 'large' },
    isDynamic: false,
    editorFields: [
      { key: 'heading', type: 'text', label: 'Heading' },
      { key: 'subheading', type: 'text', label: 'Subheading' },
      { key: 'button_text', type: 'text', label: 'Button label' },
      { key: 'button_url', type: 'url', label: 'Button link' },
    ],
  },
  spacer: {
    component: SpacerSection,
    label: 'Spacer',
    icon: '↕️',
    defaultProps: { height: 'medium' },
    defaultSettings: {},
    isDynamic: false,
    editorFields: [
      { key: 'height', type: 'select', label: 'Height', options: ['small', 'medium', 'large'] },
    ],
  },
};
