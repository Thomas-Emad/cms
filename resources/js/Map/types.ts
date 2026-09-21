/**
 * Data contract for the hotel map. Everything on screen - floors, points, routes,
 * instructions - is derived from one HotelMapData document, so a real hotel can
 * replace the demo hotel by loading different data (no UI changes).
 *
 * Coordinates are per-floor "map units" (each floor has its own width x height);
 * `meters_per_unit` converts them to real distances.
 */
export type LocationCategory =
    | 'reception' | 'lobby' | 'dining' | 'cafe' | 'wellness' | 'fitness' | 'pool'
    | 'meeting' | 'room' | 'facility' | 'transport' | 'restroom' | 'entrance' | 'other';

export type AreaKind = 'building' | 'corridor' | 'room' | 'public' | 'service' | 'water' | 'outdoor';

export interface MapArea {
    id: string;
    kind: AreaKind;
    x: number;
    y: number;
    w: number;
    h: number;
    label?: string;
}

export interface MapFloor {
    id: string;
    /** Short label for the floor selector: "B1", "G", "1". */
    label: string;
    /** Full name for cards and instructions: "2nd Floor". */
    name: string;
    /** Vertical order (-1 basement, 0 ground, 1, 2 ...). */
    level: number;
    width: number;
    height: number;
    /** Optional floor-plan image drawn under the vector areas. */
    plan_image?: string | null;
    areas: MapArea[];
}

export type NodeType = 'walk' | 'elevator' | 'stairs' | 'entrance';

export interface MapNode {
    id: string;
    floor: string;
    x: number;
    y: number;
    type: NodeType;
    /** Node ids this node is directly connected to (connections are two-way). Elevator/stairs
     *  nodes may connect to the same-type node on another floor. */
    connections: string[];
    /** Elevator/stairs only: where the cab / stairwell is. People exit facing away from it,
     *  which is how "turn left after exiting the elevator" is worked out. */
    door?: { x: number; y: number };
}

export interface MapLocation {
    id: string;
    name: string;
    description?: string | null;
    image?: string | null;
    floor: string;
    x: number;
    y: number;
    category: LocationCategory;
    opening_hours?: string | null;
    /** Node where the location's door meets the walkway. Falls back to the nearest node on the floor. */
    node?: string | null;
    /** Filled in by the server from linked hotel content (Facility / Restaurant / Room). */
    details_url?: string | null;
    ref?: { type: 'facility' | 'restaurant' | 'room'; slug: string } | null;
}

export interface HotelMapData {
    version: 1;
    meters_per_unit: number;
    /** Location id where "You are here" starts (typically the kiosk's own location). */
    default_start?: string | null;
    floors: MapFloor[];
    nodes: MapNode[];
    locations: MapLocation[];
}

/* ------------------------------ routing output ------------------------------ */

export type StepKind = 'start' | 'straight' | 'left' | 'right' | 'slight-left' | 'slight-right' | 'uturn' | 'elevator' | 'stairs' | 'exit' | 'arrive';

export interface Point {
    x: number;
    y: number;
}

export interface Transition {
    floor: string;
    toFloor: string;
    x: number;
    y: number;
    kind: 'elevator' | 'stairs';
    direction: 'up' | 'down';
}

export interface Step {
    index: number;
    kind: StepKind;
    /** Short headline, e.g. "Turn right". */
    text: string;
    /** Longer sentence, e.g. "Turn right at Cafe Aroma". */
    detail?: string;
    /** Length of the walk that FOLLOWS this instruction. */
    meters: number;
    seconds: number;
    floor: string;
    /** Where on `floor` the instruction applies. */
    at: Point;
    /** Polyline of the leg that follows, on `floor`. */
    path: Point[];
    transition?: Transition;
}

export interface RouteSegment {
    floor: string;
    points: Point[];
}

export interface Route {
    steps: Step[];
    segments: RouteSegment[];
    transitions: Transition[];
    meters: number;
    seconds: number;
    /** Floors the route touches, in visiting order. */
    floors: string[];
}

export interface Origin {
    floor: string;
    x: number;
    y: number;
    nodeId: string;
    label: string;
    /** Set when the origin is a named location (vs. a spot tapped on the map). */
    locationId?: string;
}
