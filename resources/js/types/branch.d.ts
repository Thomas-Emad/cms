import type { MediaItem } from './room';

export interface HotelBranch {
  id: number;
  hotel_id?: number;
  name: string;
  slug: string;
  domain?: string | null;
  city?: string | null;
  address?: string | null;
  phone?: string | null;
  email?: string | null;
  short_description?: string | null;
  description?: string | null;
  cover_image_url?: string | null;
  gallery_urls?: string[] | null;
  all_photos?: string[];
  all_photos_count?: number;
  features?: string[] | null;
  latitude?: number | null;
  longitude?: number | null;
  status: 'draft' | 'published' | 'archived';
  guest_layout?: 'classic' | 'tv' | null;
  metadata?: Record<string, any> | null;
  is_main: boolean;
  sort_order: number;
  translations_data?: {
    ar?: {
      name?: string;
      city?: string;
      address?: string;
      short_description?: string;
      description?: string;
    };
  };
}

export { MediaItem };
