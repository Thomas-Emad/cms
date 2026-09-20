# Cinematic Rooms / Gallery / Meeting Rooms + video

Apply on top of the previous zips. All files here REPLACE or ADD to your project.

## DELETE
- resources/js/Components/Cards/RoomCard.vue   (no longer used)

## Then
    npm run dev        (no migration this time)

## Videos: PHP upload limits
Videos are limited to 100 MB by the app, but PHP must allow it too. In Laragon: Menu > PHP > php.ini, set
    upload_max_filesize = 128M
    post_max_size = 128M
then restart Apache. Short clips (10-30 s, under ~30 MB, MP4/H.264) work best on the screens.
Videos play muted (browsers require this for autoplay). A cover/main photo must be an image.

## What changed for guests
- Rooms & Suites: full-screen story. One room per slide, slow zoom, title animates in, "View room" button.
  "See all" switches to a grid. Tap right = next, left = previous, hold = pause.
- Room page: plays as a story of its main photo + gallery photos/videos. Details below ("swipe up").
- Gallery: full-screen story of all photos/videos (videos play to the end, then advance). "See all" = tappable grid;
  tapping opens a full-screen viewer that plays videos with controls.
- Meeting Rooms: same story treatment. Facility pages (incl. meeting rooms) play as a story when they have
  more than just a cover photo.
- Home slideshow now uses the same shared player (no visible change).

## Meeting rooms: where are they?
They are Facilities with category "meeting". Admin sidebar > Content > "Meeting Rooms" lists them and
"+ Add Meeting Room" pre-selects the category. They only appear on the guest screen when Status = Published.
Add Features (projector, seats...), a main photo and gallery/videos on the same edit screen.

## Verified vs not
EXECUTED
- vite build (fonts plugin disabled in sandbox only) passes; vue-tsc: no errors in touched files.
- 16 component tests pass (vitest + jsdom): story player photos (auto-advance, loop, tap zones, hold-pause, builder
  static), VIDEO (progress follows the video not the photo timer, advances only when the video ends, hold pauses,
  single video loops), Showcase (caption/link follow slide, story<->grid, empty state), grid/viewer (video tile +
  controls, wrap-around nav), uploader (video accept, cover images only, progress, errors, delete, reorder).
  Test files are NOT in this zip.
- php -l on all changed PHP.
NOT EXECUTED
- PHPUnit/Laravel unavailable here. tests/Feature/GuestScreenContentTest.php now has 18 tests (5 new: video upload,
  video rejected as cover, extension fallback, room slides order, meeting-room admin filter). WRITTEN, NOT RUN.
- Real video playback: my tests use a stubbed video element. Codec support, autoplay and how it FEELS on your
  screens are unverified. Nothing visual has been seen by me (no browser).
