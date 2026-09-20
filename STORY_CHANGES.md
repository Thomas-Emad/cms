# Story home + new dock pages

Apply on top of the previous kiosk-layout zip (GuestLayout.vue here replaces that one).

## REPLACE
- app/Services/PageBuilder/SectionRegistry.php
- resources/js/Layouts/GuestLayout.vue
- resources/js/Pages/Guest/PageView.vue
- resources/js/PageBuilder/registry.ts
- resources/js/PageBuilder/section-types.json

## ADD
- app/Services/PageBuilder/Sections/StorySlideshowSectionDefinition.php   (type: story-slideshow)
- app/Services/PageBuilder/Sections/InfoListSectionDefinition.php         (type: info-list)
- resources/js/PageBuilder/sections/StorySlideshowSection.vue
- resources/js/PageBuilder/sections/InfoListSection.vue
- database/seeders/GuestScreenPagesSeeder.php

## THEN RUN (once)
    php artisan db:seed --class="Database\Seeders\GuestScreenPagesSeeder"
    npm run dev   (or npm run build)

The seeder is safe to re-run: existing pages are never overwritten. It
  1. turns the home page's first `hero` into a story slideshow (uses the hotel's existing media), and
  2. creates + publishes /pages/timing, /map, /short-calls, /rooms-suites, /gallery, /meeting-room.

## Dock buttons
Facilities -> /facilities (existing)   Timing, Map, Short Calls, Rooms & Suites, Gallery, Meeting Room -> /pages/<slug>
Home = hotel name / "Home" button in the top bar.

## SAMPLE CONTENT - replace in the admin Page Builder
Timing rows, Short Calls numbers and Room types are placeholders I invented.
Map has no image yet (upload a floor plan, set the Image section's media id).
Meeting Room lists facilities whose category is "meeting".

## Story slideshow behaviour
Auto-advances (default 6s, editable 3-20s), progress bar per photo, tap right = next,
tap left = previous, press-and-hold = pause, pauses when scrolled off-screen. Static in the Builder canvas.

## Verified vs not
Executed:
- vite build (fonts plugin disabled in sandbox only) - passes; vue-tsc shows no errors in touched files
- 7 component tests for the slideshow (auto-advance, loop, tap zones, hold-pause, builder-canvas static, 1 image, 0 images) - passed (test file is NOT included in this zip)
- php -l on all new/changed PHP files; PHP registry keys == section-types.json == registry.ts keys (scripted check)
NOT executed:
- Laravel/PHPUnit (not installed here): seeder and section definitions have never been run against a database
- Anything visual - no browser in my sandbox
