# Guest map: compact size

Copy over the project, then `npm run build`. No migration.

## Why it felt too large
The kiosk shell grows the base font up to 32px on big screens (16px on phones), and everything sized in Tailwind/rem grew with it,
and I had also made touch targets generous. At 1920x1080 a button was 78px tall, the search bar 90px, the panel 582px wide.

## What changed (guest map only; the map builder in the dashboard is untouched)
- The map now has **its own fixed-pixel size scale** (`.map-ui` in `resources/css/app.css`), so it no longer grows with the kiosk font.
- Everything is smaller: buttons 48px (was 56-78), search bar 48px (was 64-90), card title 22px (was 30-42),
  side panel 340px / 380px from 1280px wide / 400px from 1700px (was 416-582), floor buttons 46px, zoom buttons 44px,
  map pins 28px (was 34), tighter padding and smaller text. Touch targets stay at or above ~44px.
- **One knob:** in `resources/css/app.css`, `.map-ui { --map-scale: 1; }`. Use `0.9` for smaller, `1.15` for larger.
  The scale is 1.1 from 1700px wide and 0.95 on phones (media rules right below it).

## Not changed (shared with all guest pages)
The dark **top bar** and the bottom **dock** come from the shared kiosk layout (`GuestLayout.vue`) and also scale with the screen:
at 1920x1080 they take 112px + 168px = 280px of the height. If they also feel too big, they can be reduced for all guest pages.

## Verified vs NOT verified
Run: `vite build` OK, the built CSS contains the `.map-ui` scale and its screen-size rules; all 114 tests pass (layout logic unchanged).
NOT verified: **no browser/device.** I changed sizes by calculation, not by looking at rendered pages. Please look at your screen and tell me
if it is now right, still too big, or too small (then tweak `--map-scale`).
