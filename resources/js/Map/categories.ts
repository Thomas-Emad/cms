import type { AreaKind, HotelMapData, LocationCategory, MapFloor } from './types';

export const CATEGORY_META: Record<LocationCategory, { label: string; icon: string; color: string }> = {
    reception: { label: 'Reception', icon: '🛎️', color: '#183c2d' },
    lobby: { label: 'Lobby', icon: '🛋️', color: '#183c2d' },
    dining: { label: 'Restaurant', icon: '🍽️', color: '#b5573a' },
    cafe: { label: 'Cafe & Bar', icon: '☕', color: '#8a6a4a' },
    wellness: { label: 'Wellness', icon: '🧖', color: '#4d8a7b' },
    fitness: { label: 'Fitness', icon: '🏋️', color: '#5a6fa8' },
    pool: { label: 'Pool', icon: '🏊', color: '#3b86a8' },
    meeting: { label: 'Meeting Room', icon: '👥', color: '#6a5a9a' },
    room: { label: 'Room', icon: '🛏️', color: '#9a8f7a' },
    facility: { label: 'Facility', icon: '✨', color: '#b99a62' },
    transport: { label: 'Elevator / Stairs', icon: '↕', color: '#33413a' },
    restroom: { label: 'Restroom', icon: '🚻', color: '#6f8794' },
    entrance: { label: 'Entrance', icon: '🚪', color: '#183c2d' },
    other: { label: 'Other', icon: '📍', color: '#7a7a7a' },
};

export interface CategoryGroup {
    id: string;
    label: string;
    /** null = everything */
    categories: LocationCategory[] | null;
}

/** The quick-action chips above the map. */
export const GROUPS: CategoryGroup[] = [
    { id: 'explore', label: 'Explore', categories: null },
    { id: 'restaurants', label: 'Restaurants', categories: ['dining', 'cafe'] },
    { id: 'facilities', label: 'Facilities', categories: ['facility', 'reception', 'lobby', 'entrance'] },
    { id: 'rooms', label: 'Rooms', categories: ['room'] },
    { id: 'wellness', label: 'Wellness', categories: ['wellness', 'fitness', 'pool'] },
    { id: 'meeting', label: 'Meeting Rooms', categories: ['meeting'] },
];

export const AREA_STYLE: Record<AreaKind, { fill: string; stroke: string }> = {
    building: { fill: '#fbf9f4', stroke: '#cdbf9f' },
    corridor: { fill: '#efe8d8', stroke: 'none' },
    room: { fill: '#f1ebe0', stroke: '#ddd2bb' },
    public: { fill: '#ebe2cf', stroke: '#d6c9ad' },
    service: { fill: '#e1dcd0', stroke: '#cfc8b8' },
    water: { fill: '#cfe6ea', stroke: '#a9cfd6' },
    outdoor: { fill: '#dfe9d5', stroke: '#c3d4b5' },
};

export const ROUTE_COLOR = '#14805e';
export const YOU_COLOR = '#2b6fd6';
export const DEST_COLOR = '#b99a62';

export function floorById(data: HotelMapData, id: string): MapFloor | undefined {
    return data.floors.find((f) => f.id === id);
}

/** Glyph shown beside each instruction. */
export const STEP_ICON: Record<string, string> = {
    start: '↑',
    straight: '↑',
    exit: '↑',
    left: '←',
    right: '→',
    'slight-left': '↖',
    'slight-right': '↗',
    uturn: '↶',
    elevator: '⇅',
    stairs: '⇅',
    arrive: '✓',
};

/** "2nd Floor" -> "2nd Floor"; helper kept here so panels share one wording. */
export function ordinalFloor(name: string): string {
    return name;
}
