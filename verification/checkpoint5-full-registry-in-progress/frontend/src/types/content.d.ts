export interface Service {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  icon?: string | null;
  price?: string | null;
  request_enabled: boolean;
  contact?: string | null;
  status?: 'draft' | 'published' | 'archived';
}

export interface HotelEvent {
  id: number;
  title: string;
  slug: string;
  description?: string | null;
  start_date: string;
  end_date?: string | null;
  start_time?: string | null;
  location?: string | null;
  capacity?: number | null;
  booking_required?: boolean;
  booking_url?: string | null;
  cover_image_url?: string | null;
  gallery_urls?: string[];
  status?: 'draft' | 'published' | 'cancelled' | 'archived';
}

export interface Offer {
  id: number;
  title: string;
  slug: string;
  description?: string | null;
  price?: string | null;
  discount?: number | null;
  valid_from?: string | null;
  valid_until?: string | null;
  booking_url?: string | null;
  cover_image_url?: string | null;
  gallery_urls?: string[];
  status?: 'draft' | 'published' | 'expired' | 'archived';
  featured?: boolean;
}

export interface Experience {
  id: number;
  title: string;
  slug: string;
  description?: string | null;
  category?: string | null;
  duration?: string | null;
  price?: string | null;
  booking_url?: string | null;
  cover_image_url?: string | null;
  gallery_urls?: string[];
  status?: 'draft' | 'published' | 'archived';
  featured?: boolean;
}
