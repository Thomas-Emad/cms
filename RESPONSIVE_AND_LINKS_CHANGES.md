# Guest map: responsive fixes + "View Details" now opens the real page

Copy the files over the project (paths match), then `npm run build`. No migration.

## 1. Responsiveness (what was actually wrong)
I worked out the map area at common screens (your kiosk shell scales the root font from 16px to 32px):

| screen | before |
|---|---|
| Laptop 1366×768, 720p | the card was taller than its panel, so **Get Directions was pushed below the fold** |
| Phone | same: card taller than the bottom sheet |
| Tablet portrait 768×1024 | side-panel layout kicked in at 768px, leaving a **208 px-wide map** |
| Any short screen | Directions preview / step cards could push Start / Next off screen |

Fixed:
- **Buttons are always on screen.** Cards are now header / scrolling middle / pinned buttons. On a short screen the photo shrinks and the text scrolls; Get Directions, Start, Next, Back and Done never leave the screen.
- **Side panel only from 1024 px wide** (22rem, 26rem from 1280 px). Below that, a bottom sheet (max 62 % of the map). Portrait tablets and phones get the full-width map.
- **Spacing is measured, not guessed:** the map keeps "you are here" / the selected place clear of the *real* panel, chips, floor selector and sheet, on any screen or font scale, and updates when the window changes.
- Zoom / re-centre / set-start buttons now show on **all** screens (touch kiosks often don't pinch) and sit above the bottom sheet on small ones.
- Floor selector scrolls when there are many floors or a short screen, and keeps the active floor in view.
- Search results never taller than 45 % of the screen; long-press/scroll inside cards no longer moves the map (`overscroll-contain`).

## 2. "View Details" → the place's real page
**Why it didn't work:** a place only got a page link if it was tied, by exact slug, to a *published* Facility, Restaurant or Room. Otherwise "View Details" just expanded the card. There was no way to link a place to your **Pages**, and no warning when a link silently failed.

Now, in the map builder (select a place):
- **"View Details" opens…** a dropdown grouped by **Facilities / Restaurants / Rooms / Pages** (Page Builder pages). Unpublished ones are marked "(not published)".
- **One-click suggestion:** name a place like an existing page ("Azure Restaurant") and the builder offers "💡 Link to the restaurant …".
- **…or any page address**, e.g. `/pages/spa-menu` (or a full https:// address). It wins over the dropdown. Must start with `/` or `http(s)://`; `//host` and `javascript:` are refused.
- **Warnings** (Checks tab + under the field) when a link points to a draft or deleted page, so it can't fail silently. Bad addresses block Save.
- Guests: **View Details** opens the page inside the app; a full web address opens in a new tab. A place with no page shows an honest **"More info"** (expands the card) instead of a fake "View Details".
- Only **published** pages/content are ever linked (a draft has no guest URL, so no button to a 404).
- New: `/map?place=<id>` opens the map with that place selected (for QR codes, or a "show on map" link on any page).

## Files
REPLACE: `MapDataValidator.php`, `MapContentLinker.php`, Guest + Admin `MapController.php`, `Map/types.ts`, `LocationSheet.vue`, `DirectionsPanel.vue`, `MapExperience.vue`, `SearchPanel.vue`, `FloorSelector.vue`, `Map/builder/*`, `Pages/Guest/Map.vue`, `Pages/Admin/Map/Builder.vue`, `tests/Feature/HotelMapTest.php` (+6 tests)
ADD: `tests/frontend-map/guestdetails.test.ts` (+ updated builder/builderui/experience tests)

## Verified vs NOT verified
Actually run: `vite build` OK and the generated CSS contains the new layout classes; **114 Vitest tests pass** (13 new: link types on the card, "More info", buttons outside the scrolling body, `?place=`, measured camera padding, dropdown groups, page refs, draft/deleted warnings, suggestion, typed address saved/blocked); validator run with plain `php` (page refs, safe/unsafe links: 9 new checks); real strict type-check: **0 errors in Map/builder files** (the 14 older errors elsewhere are unchanged). A real bug was caught and fixed by the new tests ("Facilitys" label).
NOT verified: **no browser or device**: I computed the layout arithmetic and tested the logic, but haven't seen these screens rendered; please check your kiosk, a laptop and a phone. The 6 new PHPUnit tests are written, not run.

## If this isn't what you meant
"Not good response" could mean something other than screen layout (e.g. slow to respond while dragging). If so, tell me which screen/device and what you see, and I'll look at that specifically.
