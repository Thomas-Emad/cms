export interface MenuItem {
  id: number;
  name: string;
  description?: string | null;
  price: string;
  dietary_info?: string[] | null;
  is_available: boolean;
  cover_image_url?: string | null;
}

export interface MenuCategory {
  id: number;
  name: string;
  items: MenuItem[];
}

export interface Menu {
  id: number;
  name: string;
  categories: MenuCategory[];
}

export interface Restaurant {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  cuisine?: string | null;
  location?: string | null;
  floor?: string | null;
  dress_code?: string | null;
  phone?: string | null;
  reservation_url?: string | null;
  cover_image_url?: string | null;
  gallery_urls?: string[];
  status: 'draft' | 'published' | 'archived';
  featured?: boolean;
  active_menu?: Menu | null;
}
