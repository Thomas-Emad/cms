import defaults from './layout-defaults.json';

export type TemplateId = 'classic' | 'tv';
export type TileSize = 'small' | 'medium' | 'large';

export interface MenuItem {
    href: string;
    label: string;
    icon: string;
    /** Tile colour (Smart TV template); null = neutral. */
    color: string | null;
    visible: boolean;
}

export interface GuestLayoutConfig {
    template: TemplateId;
    show_clock: boolean;
    /** Smart TV: the home screen fills the screen and never scrolls. */
    lock_home_scroll: boolean;
    tile_size: TileSize;
    tagline: string;
    headline: string;
    items: MenuItem[];
}

/** Same file the server reads, so the two sides always agree on the defaults. */
export const DEFAULT_CONFIG = defaults as GuestLayoutConfig;

export interface TemplateInfo {
    id: TemplateId;
    name: string;
    description: string;
}

export const TEMPLATES: TemplateInfo[] = [
    { id: 'classic', name: 'Classic dock', description: 'A slim top bar and a dock of buttons at the bottom. Pages scroll normally.' },
    { id: 'tv', name: 'Smart TV', description: 'A full-screen home that never scrolls, with a row of big tiles like a TV launcher. Other pages scroll.' },
];

/** Tile size in rem (scaled with the screen by the kiosk root font size). */
export const TILE_DIMENSIONS: Record<TileSize, { w: number; h: number }> = {
    small: { w: 9.5, h: 4 },
    medium: { w: 11.5, h: 5 },
    large: { w: 14, h: 6 },
};

const isHex = (c: unknown): c is string => typeof c === 'string' && /^#[0-9a-f]{6}$/i.test(c);

/** Turn whatever the server sent (or nothing) into a complete config; anything odd falls back to the defaults. */
export function resolveConfig(raw: unknown): GuestLayoutConfig {
    const r = (raw && typeof raw === 'object' ? raw : {}) as Partial<GuestLayoutConfig>;
    const items = Array.isArray(r.items)
        ? r.items
              .filter((i) => i && typeof i.href === 'string' && i.href.startsWith('/') && typeof i.label === 'string' && i.label.trim() !== '')
              .map((i) => ({ href: i.href, label: i.label, icon: typeof i.icon === 'string' ? i.icon : '', color: isHex(i.color) ? i.color : null, visible: i.visible !== false }))
        : [];
    return {
        template: r.template === 'tv' ? 'tv' : 'classic',
        show_clock: typeof r.show_clock === 'boolean' ? r.show_clock : DEFAULT_CONFIG.show_clock,
        lock_home_scroll: typeof r.lock_home_scroll === 'boolean' ? r.lock_home_scroll : DEFAULT_CONFIG.lock_home_scroll,
        tile_size: r.tile_size === 'small' || r.tile_size === 'large' ? r.tile_size : 'medium',
        tagline: typeof r.tagline === 'string' ? r.tagline : '',
        headline: typeof r.headline === 'string' ? r.headline : '',
        items: items.length ? items : DEFAULT_CONFIG.items.map((i) => ({ ...i })),
    };
}

export const visibleItems = (c: GuestLayoutConfig) => c.items.filter((i) => i.visible);

export const ROUTE_LABELS: Record<string, string> = {
    '/facilities': 'nav.facilities',
    '/timing': 'nav.timing',
    '/map': 'nav.hotel_map',
    '/short-calls': 'nav.short_calls',
    '/rooms': 'nav.rooms',
    '/gallery': 'nav.gallery',
    '/meeting-rooms': 'nav.meeting_rooms',
    '/weather': 'nav.weather',
    '/branches': 'nav.branches',
    '/restaurants': 'nav.restaurants',
    '/services': 'nav.services',
    '/events': 'nav.events',
    '/offers': 'nav.offers',
    '/experiences': 'nav.experiences',
};

export function readableOn(hex: string | null): '#ffffff' | '#111111' {
    if (!isHex(hex)) return '#ffffff';
    const n = parseInt(hex.slice(1), 16);
    const lin = (v: number) => {
        const s = v / 255;
        return s <= 0.03928 ? s / 12.92 : ((s + 0.055) / 1.055) ** 2.4;
    };
    const L = 0.2126 * lin((n >> 16) & 255) + 0.7152 * lin((n >> 8) & 255) + 0.0722 * lin(n & 255);
    return L > 0.4 ? '#111111' : '#ffffff';
}

export function localizedItemLabel(
    item: { href: string; label: string },
    translate: (key: string, params?: Record<string, string | number>, fallback?: string) => string,
): string {
    const key = ROUTE_LABELS[item.href];
    if (key) {
        return translate(key, undefined, item.label);
    }
    return item.label;
}



