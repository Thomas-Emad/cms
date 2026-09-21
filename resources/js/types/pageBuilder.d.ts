export interface Section<P = Record<string, unknown>, D = Record<string, unknown>> {
  id: string;
  type: string;
  props: P;
  settings: SectionSettings;
  data?: D; // populated by PageRenderService server-side, absent while editing pre-resolve
}

export interface SectionSettings {
  background?: 'light' | 'dark' | 'brand' | 'transparent';
  padding?: 'none' | 'small' | 'medium' | 'large';
  [key: string]: unknown; // some section types add e.g. columns/layout here
}

export interface HeroProps {
  title: string;
  subtitle?: string;
  media_id?: number | null;
  button_text?: string;
  button_url?: string;
}

export interface AppLauncherProps {
  eyebrow?: string;
  title?: string;
  subtitle?: string;
  media_id?: number | null;
  // One app tile per line: "Label | URL" (URL optional). See
  // AppLauncherSectionDefinition's docblock - same convention as
  // info-list's items_text, parsed client-side in AppLauncherSection.vue.
  apps_text?: string;
}

export interface TextProps {
  heading?: string;
  body?: string;
}

export interface FacilityGridProps {
  title?: string;
  description?: string | null;
  category?: string | null;
  featured_only?: boolean;
  limit?: number;
  columns?: 2 | 3 | 4;
}

export interface FacilityGridData {
  facilities: {
    id: number;
    name: string;
    slug: string;
    short_description?: string | null;
    category: string;
    cover_image_url?: string | null;
  }[];
}

export type RenderMode = 'edit' | 'preview' | 'live';
