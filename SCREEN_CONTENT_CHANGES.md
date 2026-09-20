# Timing, Short Calls, Rooms & Suites, Gallery, Meeting Rooms (admin-managed)

Apply on top of the previous zips (story-pages, kiosk-layout). Copy every file in this zip over your project.
routes/web.php, GuestLayout.vue, AdminLayout.vue, Facilities Edit/Index/Show, both Facility controllers,
Hotel.php, Media.php and GuestScreenPagesSeeder.php REPLACE existing files (diff them first if you edited them).

## Then run
    php artisan migrate
    php artisan storage:link          # once - uploaded photos are served from /storage
    npm run dev

APP_URL in .env must match the address you open the site at (e.g. http://localhost:8000),
because uploaded image URLs are built from it.

## What each dock button opens
Facilities    /facilities        (existing)
Timing        /timing            admin: Timing            (sidebar > Content)
Map           /pages/map         placeholder - tappable map is NOT built yet (next)
Short Calls   /short-calls       admin: Short Calls
Rooms&Suites  /rooms             admin: Rooms & Suites (photos, gallery, features)
Gallery       /gallery           admin: Gallery (upload, reorder)
Meeting Room  /meeting-rooms     = Facilities with category "meeting" (features + gallery via Facility edit)

## Admin
- Timing: rows of Group / Label / Value (e.g. Hotel | Check-in | 15:00). Rows with the same group show together.
- Short Calls: rows of Label / Number (e.g. IT Support | 15, Helper | 59).
- Rooms: name, size, guests, bed, view, description, features, main photo, gallery. New rooms open the edit
  screen after Save so photos can be added.
- Facilities form now has Features (amenities) and photo upload. Creating a facility also lands on its edit screen.
- No sample data is created. Guest pages show a friendly empty message until you add data.
- Hotel gallery uploads need hotel_admin (or super_admin); hotel_staff can manage rooms/facilities photos.

## Guest
- Tap any gallery/room/facility photo to open a full-screen viewer (swipe or big arrows, tap X to close).
- Room and facility detail pages have a Back button (the screen has no browser back).

## Cleanup (optional)
The earlier seeder created sample pages /pages/timing, /short-calls, /rooms-suites, /gallery, /meeting-room.
They are no longer linked. Delete or unpublish them in admin > Pages. The Info List section type from
the previous zip is still available in the Builder but is no longer used by these pages.

## Verified vs not
EXECUTED
- vite build (fonts plugin disabled in sandbox only): passes. vue-tsc: no errors in any touched file.
- 15 component tests (vitest + jsdom) pass: story slideshow (7), upload manager (5: upload fields, cover replaces,
  server errors, delete, reorder+revert), photo viewer (2: open/next/prev wrap/close, single image), feature tag input (1).
  These test files are NOT in this zip.
- php -l on every new/changed PHP file.
NOT EXECUTED
- Laravel / PHPUnit is not installed here. tests/Feature/GuestScreenContentTest.php (13 tests: tenant isolation on
  save + upload + delete, cover replace, non-image rejection, reorder, empty list, guest pages) is WRITTEN, NOT RUN.
- Migrations, controllers and actual file storage have never run against MySQL/Laravel by me.
- Nothing visual: no browser in my sandbox.
