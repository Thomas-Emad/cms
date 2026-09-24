import type { Component } from 'vue';
import HeroSection from './sections/HeroSection.vue';
import AppLauncherSection from './sections/AppLauncherSection.vue';
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
import StorySlideshowSection from './sections/StorySlideshowSection.vue';
import InfoListSection from './sections/InfoListSection.vue';
import RestaurantHeroSection from './sections/RestaurantHeroSection.vue';
import RestaurantInfoSection from './sections/RestaurantInfoSection.vue';
import RestaurantGallerySection from './sections/RestaurantGallerySection.vue';
import RestaurantMenuSection from './sections/RestaurantMenuSection.vue';
import RestaurantLocationSection from './sections/RestaurantLocationSection.vue';

export interface EditorField {
  key: string;
  type: 'text' | 'textarea' | 'number' | 'toggle' | 'select' | 'media' | 'media-list' | 'url';
  label: string;
  options?: (string | number)[];
  min?: number;
  max?: number;
}

/** 'any' = shown regardless of Builder context. Mirrors the backend
 * SectionRegistry's $contexts map - kept in sync by convention, same as
 * the type keys themselves (see SectionRegistryParityTest). */
export type SectionContext = 'page' | 'restaurant' | 'any';

export interface SectionRegistryEntry {
  component: Component;
  label: string;
  icon: string;
  defaultProps: Record<string, unknown>;
  defaultSettings: Record<string, unknown>;
  isDynamic: boolean;
  contexts: SectionContext[];
  editorFields: EditorField[];
}

const FACILITY_CATEGORIES = ['wellness', 'fitness', 'pool', 'kids', 'business', 'beach', 'meeting', 'other'];

export const SECTION_REGISTRY: Record<string, SectionRegistryEntry> = {
  hero: {
    component: HeroSection, label: 'Hero', icon: '🖼️', contexts: ['page'],
    defaultProps: { eyebrow: '', title: '', subtitle: '', media_id: null, button_text: '', button_url: '' },
    defaultSettings: { background: 'transparent', padding: 'large' }, isDynamic: false,
    editorFields: [
      { key: 'eyebrow', type: 'text', label: 'Eyebrow' },
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'subtitle', type: 'text', label: 'Subtitle' },
      { key: 'media_id', type: 'media', label: 'Background image' },
      { key: 'button_text', type: 'text', label: 'Button label' },
      { key: 'button_url', type: 'url', label: 'Button link' },
    ],
  },

  'app-launcher': {
    component: AppLauncherSection, label: 'App Launcher (TV Home Screen)', icon: '📺', contexts: ['page'],
    defaultProps: {
      eyebrow: '', title: '', subtitle: '', media_id: null,
      apps_text: 'Apps\nYouTube | https://youtube.com\nNetflix | https://netflix.com\nPrime Video | https://primevideo.com\nDisney+ | https://disneyplus.com',
    },
    defaultSettings: { padding: 'none' }, isDynamic: false,
    editorFields: [
      { key: 'eyebrow', type: 'text', label: 'Eyebrow' },
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'subtitle', type: 'text', label: 'Subtitle' },
      { key: 'media_id', type: 'media', label: 'Background image' },
      { key: 'apps_text', type: 'textarea', label: 'App tiles - one per line: Label | URL (URL optional)' },
    ],
  },
  text: {
    component: TextSection, label: 'Text', icon: '📝', contexts: ['any'],
    defaultProps: { heading: '', body: '' }, defaultSettings: { padding: 'medium' }, isDynamic: false,
    editorFields: [
      { key: 'heading', type: 'text', label: 'Heading' },
      { key: 'body', type: 'textarea', label: 'Body text' },
    ],
  },
  image: {
    component: ImageSection, label: 'Image', icon: '🖼️', contexts: ['any'],
    defaultProps: { media_id: null, caption: '', link_url: '' }, defaultSettings: { padding: 'medium' }, isDynamic: false,
    editorFields: [
      { key: 'media_id', type: 'media', label: 'Image' },
      { key: 'caption', type: 'text', label: 'Caption' },
      { key: 'link_url', type: 'url', label: 'Link (optional)' },
    ],
  },
  gallery: {
    component: GallerySection, label: 'Gallery', icon: '🎞️', contexts: ['page'],
    defaultProps: { title: '', media_ids: [] }, defaultSettings: { padding: 'medium', layout: 'grid' }, isDynamic: false,
    editorFields: [
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'media_ids', type: 'media-list', label: 'Images' },
    ],
  },
  'facility-grid': {
    component: FacilityGridSection, label: 'Facility Grid', icon: '🏨', contexts: ['page'],
    defaultProps: { title: 'Explore Our Facilities', description: null, category: null, featured_only: false, limit: 6, columns: 3 },
    defaultSettings: { background: 'light', padding: 'medium' }, isDynamic: true,
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
    component: RestaurantGridSection, label: 'Restaurant Grid', icon: '🍽️', contexts: ['page'],
    defaultProps: { title: 'Dining at Grand Horizon', description: null, cuisine: null, featured_only: false, limit: 6 },
    defaultSettings: { background: 'light', padding: 'medium' }, isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      { key: 'cuisine', type: 'text', label: 'Cuisine filter (optional)' },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  'service-grid': {
    component: ServiceGridSection, label: 'Service Grid', icon: '🛎️', contexts: ['page'],
    defaultProps: { title: 'Hotel Services', limit: 6 }, defaultSettings: { padding: 'medium' }, isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  events: {
    component: EventsSection, label: 'Events', icon: '📅', contexts: ['page'],
    defaultProps: { title: 'Upcoming Events', limit: 4, upcoming_only: true }, defaultSettings: { padding: 'medium' }, isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'upcoming_only', type: 'toggle', label: 'Upcoming only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  offers: {
    component: OffersSection, label: 'Offers', icon: '🏷️', contexts: ['page'],
    defaultProps: { title: 'Special Offers', limit: 4, active_only: true, featured_only: false }, defaultSettings: { padding: 'medium' }, isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'active_only', type: 'toggle', label: 'Active only (hide expired)' },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  experiences: {
    component: ExperiencesSection, label: 'Experiences', icon: '🧘', contexts: ['page'],
    defaultProps: { title: 'Experiences', description: null, category: null, featured_only: false, limit: 4 },
    defaultSettings: { background: 'light', padding: 'medium' }, isDynamic: true,
    editorFields: [
      { key: 'title', type: 'text', label: 'Section title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      { key: 'category', type: 'text', label: 'Category filter (optional)' },
      { key: 'featured_only', type: 'toggle', label: 'Featured only' },
      { key: 'limit', type: 'number', label: 'Number to show', min: 1, max: 12 },
    ],
  },
  cta: {
    component: CtaSection, label: 'Call to Action', icon: '📣', contexts: ['any'],
    defaultProps: { heading: '', subheading: '', button_text: '', button_url: '' }, defaultSettings: { background: 'brand', padding: 'large' }, isDynamic: false,
    editorFields: [
      { key: 'heading', type: 'text', label: 'Heading' },
      { key: 'subheading', type: 'text', label: 'Subheading' },
      { key: 'button_text', type: 'text', label: 'Button label' },
      { key: 'button_url', type: 'url', label: 'Button link' },
    ],
  },
  spacer: {
    component: SpacerSection, label: 'Spacer', icon: '↕️', contexts: ['any'],
    defaultProps: { height: 'medium' }, defaultSettings: {}, isDynamic: false,
    editorFields: [{ key: 'height', type: 'select', label: 'Height', options: ['small', 'medium', 'large'] }],
  },

  'story-slideshow': {
    component: StorySlideshowSection, label: 'Story Slideshow', icon: '🎬', contexts: ['page'],
    defaultProps: { title: '', subtitle: '', media_ids: [], interval_seconds: 6 }, defaultSettings: {}, isDynamic: false,
    editorFields: [
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'subtitle', type: 'text', label: 'Subtitle' },
      { key: 'media_ids', type: 'media-list', label: 'Images (in order)' },
      { key: 'interval_seconds', type: 'number', label: 'Seconds per photo', min: 3, max: 20 },
    ],
  },
  'info-list': {
    component: InfoListSection, label: 'Info List', icon: '📋', contexts: ['any'],
    defaultProps: { title: '', description: '', items_text: '' }, defaultSettings: { padding: 'medium' }, isDynamic: false,
    editorFields: [
      { key: 'title', type: 'text', label: 'Title' },
      { key: 'description', type: 'textarea', label: 'Description' },
      { key: 'items_text', type: 'textarea', label: 'Rows - one per line: Label | Value (a line without | is a group heading)' },
    ],
  },

  // --- Entity-backed: Restaurant ---
  'restaurant-hero': {
    component: RestaurantHeroSection, label: 'Restaurant Hero', icon: '🍽️', contexts: ['restaurant'],
    defaultProps: { subtitle_override: '', button_text: 'Reserve a Table' }, defaultSettings: {}, isDynamic: true,
    editorFields: [
      { key: 'subtitle_override', type: 'text', label: 'Subtitle (optional override)' },
      { key: 'button_text', type: 'text', label: 'Button label' },
    ],
  },
  'restaurant-info': {
    component: RestaurantInfoSection, label: 'Restaurant Info', icon: 'ℹ️', contexts: ['restaurant'],
    defaultProps: { show_opening_hours: true }, defaultSettings: {}, isDynamic: true,
    editorFields: [{ key: 'show_opening_hours', type: 'toggle', label: 'Show opening hours' }],
  },
  'restaurant-gallery': {
    component: RestaurantGallerySection, label: 'Restaurant Gallery', icon: '🎞️', contexts: ['restaurant'],
    defaultProps: { title: 'Gallery' }, defaultSettings: {}, isDynamic: true,
    editorFields: [{ key: 'title', type: 'text', label: 'Section title' }],
  },
  'restaurant-menu': {
    component: RestaurantMenuSection, label: 'Restaurant Menu', icon: '📋', contexts: ['restaurant'],
    defaultProps: { title: 'Menu' }, defaultSettings: {}, isDynamic: true,
    editorFields: [{ key: 'title', type: 'text', label: 'Section title' }],
  },
  'restaurant-location': {
    component: RestaurantLocationSection, label: 'Restaurant Location', icon: '📍', contexts: ['restaurant'],
    defaultProps: {}, defaultSettings: {}, isDynamic: true, editorFields: [],
  },
};

export function sectionsForContext(context: SectionContext): [string, SectionRegistryEntry][] {
  return Object.entries(SECTION_REGISTRY).filter(
    ([, entry]) => entry.contexts.includes(context) || entry.contexts.includes('any')
  );
}
