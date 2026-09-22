# Guest-screen layout templates + admin chooser

Admin → **Guest Layout** now lets you pick between two full templates and edit the menu, with a live preview. Everything applies immediately, no republish step.

## Install
Copy the files over the project (paths match), then `npm run build`. **No migration** — reuses the existing `hotel_settings.metadata` JSON column (adds one key, `guest_layout`; everything else in there is left untouched).

## The two templates
- **Classic dock** — your current layout, unchanged pixel-for-pixel: slim dark top bar, bottom dock of pill buttons, every page scrolls normally.
- **Smart TV** — modelled on the screenshot you sent: the **home page fills the whole screen and does not scroll** (a picture/slideshow behind everything, hotel name top-left, optional headline and tagline), with a row of **big colour tiles** along the bottom, TV-launcher style. Every *other* page scrolls normally with a slimmer bar and a smaller tile row, so content pages (Facilities, Gallery, the Map, etc.) still work exactly as before — the "no scrolling" is only the home screen. Admin controls: tile size (small/medium/large), each tile's colour/icon/label/link, whether the home lock is on, headline, tagline, clock on/off.

Adding a third template later means: a new shell component in `resources/js/Layouts/guest/`, one line in `shellConfig.ts`, one line in `GuestLayout.vue`, and its id added to `GuestLayoutConfig::TEMPLATES` — the switcher, storage and validation don't change.

## The menu editor (works for both templates)
Add, rename, reorder (▲▼), show/hide, delete items; each has a page address, a name, an emoji/icon, and (Smart TV only) a tile colour. A live preview on the right shows Home and "Another page" for whichever template is selected, scaled to fit. "Reset to defaults" restores the original 7 items.

## How it's stored / shared
`GuestLayoutConfig` (pure PHP, no framework dependency) defines the shape and validates it; the same defaults live in one JSON file (`resources/js/Layouts/guest/layout-defaults.json`) that both the server and the frontend read, so they can't drift apart. `GuestLayoutStore` reads/writes it inside `hotel_settings.metadata['guest_layout']`. `HandleInertiaRequests` shares it to every guest page as `guestLayout` (never queried in `/admin*`, so it costs nothing there); `GuestLayout.vue` picks `ClassicShell` or `TvShell` from it. A missing/broken/half-edited value always falls back to sensible defaults — this can never produce a blank guest screen.

## Files
ADD: `app/Services/Layout/{GuestLayoutConfig,GuestLayoutStore}.php`, `app/Http/Controllers/Admin/LayoutController.php`, `resources/js/Layouts/guest/{shellConfig.ts, useKioskShell.ts, ClassicShell.vue, TvShell.vue, layout-defaults.json}`, `resources/js/Pages/Admin/Layout/Edit.vue`, `tests/Feature/GuestLayoutTest.php`, `tests/frontend-layout/*`
REPLACE: `HandleInertiaRequests.php` (+`guestLayout` shared prop), `routes/web.php` (+2 admin routes), `AdminLayout.vue` (nav entry), `GuestLayout.vue` (now a 20-line switcher; the old markup moved unchanged into `ClassicShell.vue`), `resources/css/app.css` (+tile styles)

## Verified vs NOT verified
**Actually run in the sandbox**
- `vite build` passes. Real strict `vue-tsc` (TypeScript 5.7 + vue-tsc 2.2, since this project has no tsconfig and its own TypeScript 7 is incompatible): **0 new errors** — same 14 pre-existing errors as before this work, in unrelated files.
- `GuestLayoutConfig` executed with plain `php`: **22 checks**, all passing (defaults valid, garbage safely falls back, every kind of bad menu item rejected: bad/duplicate/unsafe href, empty label, bad colour, too many items, no visible items, oversized text).
- **141 Vitest tests pass, no regressions** (27 new): the frontend config resolver cross-checked against the **real PHP validator** for every template/tile-size/edited-config combination; `ClassicShell` (unchanged: dock, clock, idle-timeout, font scaling); `TvShell` (home lock/unlock and restore on unmount, only-home elements hidden on other pages, tile sizing, **arrow-key/remote navigation between tiles**, idle timeout); the template switcher; the admin editor driven with **real form submit events** (add/rename/reorder/hide/delete an item, switch template, save, server errors, reset).

**NOT verified**
- **No browser, TV, or remote control was available.** I have not seen either template rendered, the "no scroll" home screen, the tile focus/hover animation, or arrow-key navigation on a real TV remote. Please check on your kiosk and, if you have one, an actual TV.
- The 6 new PHPUnit tests (defaults, per-hotel isolation, save-and-share, validation, admin-area never gets the prop, auth) are **written, not run** — Laravel/Composer aren't installable here.

## Known limits
- Smart TV's arrow-key navigation moves between tiles only; it doesn't scroll *within* a content page (a remote's up/down would need per-page work, since pages are ordinary scrolling HTML).
- Only two templates ship. A drag-and-drop tile layout (arbitrary positions/sizes) would be a further feature, similar in scope to the map builder.
- The headline/tagline are single lines of text only (no rich formatting).
