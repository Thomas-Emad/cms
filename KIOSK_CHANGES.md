# Touch-screen layout — what changed

## REPLACE
- resources/js/Layouts/GuestLayout.vue  (full rewrite)
- resources/css/app.css                  (only appended a "Touch-screen hardening" block at the end)

## OPTIONAL CLEANUP
- resources/js/lib/headerState.ts is no longer used. Delete it.

## Behaviour
- Top bar: always dark, hotel name + clock. Works on pages with or without a hero.
- Bottom dock: big pill buttons (Home, Dining, Facilities, Services, Events, Offers, Experiences),
  active page highlighted, scrolls sideways if they do not fit.
- Idle reset: after 90s with no touch, returns to "/" (or scrolls to top if already there). Change IDLE_MS.
- Root font size scales with screen width (clamp 16px..32px) while guest pages are mounted; restored on leave.
- Footer removed (not useful on a public screen).
- Long-press menu, text selection, double-tap zoom and overscroll bounce disabled on guest pages.

## Sizes to tweak
--kiosk-topbar-h (5rem) and --kiosk-dock-h (7.5rem) on the root div in GuestLayout.vue.

## Verified vs not
- Executed: vite build (with the laravel fonts plugin disabled, because my sandbox cannot reach fonts.bunny.net). Passed.
- NOT verified: anything visual. No browser here. Check on the real screen.
- Not touched: pages, sections, PHP, tests.
