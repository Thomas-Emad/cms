export interface Hotel {
  id: number;
  name: string;
  slug?: string;
  status?: 'active' | 'suspended' | 'draft';
}

export interface Theme {
  primaryColor: string;
  secondaryColor: string;
  fontFamily: string;
  borderRadius: 'none' | 'small' | 'medium' | 'large' | 'full';
  buttonStyle: 'square' | 'rounded' | 'pill';
  cardStyle: 'flat' | 'outlined' | 'elevated';
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: 'super_admin' | 'hotel_admin' | 'hotel_staff';
  hotel_id: number | null;
}

/** Shape of Inertia's shared props, extended per-page as needed. */
export interface SharedPageProps {
  auth: {
    user: AuthUser | null;
  };
  hotel?: Hotel;
  // Site-wide guest shell choice from Admin > Settings > Guest View
  // (HotelSettings.guest_view) - see GuestShell.vue.
  guestView?: 'classic' | 'tv';
}
