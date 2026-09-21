# Map Builder — edit the hotel map visually in the dashboard

Admin → Content → **Hotel Map** now opens the **Map builder** (`/admin/map/builder`). No JSON needed.
The old JSON page is still there as **Advanced (JSON)**. Both edit the same document the guest map uses,
and both go through the same server validation.

## Install
Copy the files over the project (paths match), then `npm run build`. **No new migration** (uses the existing `hotel_maps` table).
This zip contains only what is new or changed since `grand-horizon-hotel-map.zip`.

## What anyone can do
- **Floors:** add (optionally *copy the layout of another floor*: rooms, corridors, lifts and stairs come along, already joined), rename, set level, delete (undoable), optional floor-plan image URL to trace over.
- **Area tool:** drag to draw rooms, halls, pools, outlines. Drag to move; drag the corner handles to resize; pick the kind and label.
- **Walkway tool:** click along corridors to lay points; each click joins to the previous one. Click an existing point to join or continue from it. "Straight walkways" keeps corridors square; points snap to the grid and magnetically to existing points.
- **Place tool:** click inside a room to add a restaurant, room, spa… Name it, describe it, set hours and photo, and optionally **show content from your site** (pick a published Facility / Restaurant / Room: the guest card then gets its photo, text and View Details link). Tick **"This is where the kiosk is"** for "You are here".
- **Doors:** each place joins the nearest walkway automatically and follows the place when moved; or use "Choose the door on the map".
- **Lifts & stairs:** select one → **Connect to all floors**. Missing floors get a matching lift in the same spot. Say which side of the corridor the lift is on, so guests hear the right "turn left/right after exiting".
- **Undo / redo** (Ctrl+Z / Ctrl+Shift+Z, 100 steps; a whole drag is one step), delete (Delete key), shortcuts V / A / W / P, Ctrl+S to save, Esc to step back. Unsaved-changes warning if you try to leave.
- **Checks** (live): unnamed places, floors with places but no walkway, places nobody can walk to, unconnected lifts, no kiosk chosen. Click a problem to jump to it. Errors block **Save**; warnings don't.
- **Test a route:** pick any two places and see the distance and the exact wording guests will get, with the route drawn on the plan. Do this before saving.
- Works with touch: drag to pan, pinch/wheel to zoom.

## Files
ADD: `resources/js/Map/builder/{useMapBuilder.ts, BuilderCanvas.vue, BuilderInspector.vue}`, `resources/js/Pages/Admin/Map/Builder.vue`, `tests/frontend-map/{builder,builderui}.test.ts`, `tests/frontend-map/inertia-stub.ts`
REPLACE: `app/Http/Controllers/Admin/MapController.php` (+`builder`, `save`), `routes/web.php` (+2 admin routes), `Layouts/AdminLayout.vue` (nav → builder), `Pages/Admin/Map/Edit.vue` (links to the builder), `tests/Feature/HotelMapTest.php` (+6 tests)

## Verified vs NOT verified
**Actually run in the sandbox**
- `vite build` passes.
- 101 Vitest tests pass in total (no regressions). New: 19 for the editor logic (every edit, undo/redo, lift linking, floor copy, checks, door picking) and 14 that drive the real builder page with **pointer and keyboard events** (draw an area by dragging, lay walkway points, place and delete a place, drag-to-move + undo, add a floor by copying, connect a lift, checks, blocked save, test route, Save sends the whole map to `/admin/map/save`).
- **The editor's output was validated by the server's PHP validator** (executed with plain `php`) after: blank map, copied floors, deleted floors/points, changed lift types, a fully edited map. So the builder cannot produce a document the server would reject, except for the cases the Checks panel already blocks.
- The real editing canvas was rendered to an image and looked at (selected room with handles, walkway points/edges, linked lift, test route).
- **Type-check:** with a real strict `vue-tsc` run (see correction below), **0 errors in every Map and builder file**.

**NOT verified**
- **No browser / touch device was available.** Real drag feel, hit-target sizes, and performance on a tablet were not seen. Pointer logic is tested with synthetic events.
- Laravel/PHPUnit can't run here: the 6 new backend tests (builder page content, save, "" → null middleware, refused save keeps the old map, auth) are **written, not run**.

## Correction to the previous delivery notes
`MAP_CHANGES.md` said "`vue-tsc --noEmit`: 0 errors project-wide". That was **not a valid check**: the sandbox copy of the project has no `tsconfig.json`, and its TypeScript 7 is incompatible with `vue-tsc`, so the command crashed silently and I counted "no error lines" as "no errors". I have now run a real strict check (TypeScript 5.7 + vue-tsc 2.2, temporary tsconfig with your `@/` alias): **0 errors in all Map/builder files** (previous and new). The same run shows 14 pre-existing errors in other files (Inertia `PageProps` typing and the `route()` global in `AdminLayout.vue`, restaurant/page-builder components, `Login.vue`, `app.ts`): they are not from the map work, and I left them alone. If your project has its own tsconfig, its result is the authority.

## Known limits
- Rooms are **rectangles** (no polygons/curves) and walkway points are joined by straight lines.
- Floor plan image is by **URL** (no upload button yet); the coordinate space per floor is 1000 × 600 units (1 unit = 0.1 m) unless you edit the JSON.
- Two admins editing at once: last save wins (no live merge).
- Each save replaces the whole map; there is no version history beyond in-session undo.
