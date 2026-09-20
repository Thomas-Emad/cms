export interface MediaItem {
  id: number;
  url: string;
  alt_text?: string | null;
}

export interface Room {
  id: number;
  name: string;
  slug: string;
  short_description?: string | null;
  description?: string | null;
  size_sqm?: number | null;
  max_guests?: number | null;
  bed_type?: string | null;
  view?: string | null;
  features?: string[] | null;
  status: 'draft' | 'published' | 'archived';
  featured?: boolean;
  sort_order?: number;
  cover_image_url?: string | null;
}
