export type FacilityCategory =
  | 'wellness' | 'fitness' | 'pool' | 'kids' | 'business' | 'beach' | 'meeting' | 'other';

export type ContentStatus = 'draft' | 'published' | 'archived';

export interface Facility {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  short_description?: string | null;
  category: FacilityCategory;
  cover_image_url?: string | null;
  building?: string | null;
  floor?: string | null;
  wing?: string | null;
  pos_x?: number | null;
  pos_y?: number | null;
  opening_hours?: Record<string, string> | null;
  phone?: string | null;
  email?: string | null;
  amenities?: string[] | null;
  status: ContentStatus;
  featured?: boolean;
  sort_order: number;
}

export interface Paginated<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
  current_page: number;
  last_page: number;
}
