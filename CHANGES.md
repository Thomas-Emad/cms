# Motion Consistency Pass — Changes

## Files to DELETE from your project
- `resources/js/Pages/Guest/Home.vue` (dead code — confirmed `/` routes through `PageController@home`, never through this file)
- `app/Http/Controllers/Guest/HomeController.php` (dead code — confirmed no route references it)

## Files to REPLACE (all included in this zip)
- `resources/css/app.css` — new `.reveal-mask`/`.reveal-mask-inner` masked-text-reveal primitive; page transition upgraded from a plain vertical fade to opacity+scale+clip-path, using `--duration-fast` (was incorrectly using the slower `--duration-base`, which is part of why navigation could feel sluggish); reduced-motion block extended to cover the new primitives.
- `resources/js/lib/motion.ts` — added `vRevealMount`, a mount-triggered sibling to the existing scroll-triggered `vReveal`, specifically for above-the-fold content (heroes) where scroll-intersection either never fires or fires instantly with no ability to sequence.
- `resources/js/app.ts` — registered `v-reveal-mount` as a **global** directive (matching how `v-reveal` was already registered), so it never needs a per-file import. This directly prevents the bug found and fixed in this pass (see below).
- `resources/js/PageBuilder/sections/HeroSection.vue` — **rewritten**. Found and fixed a real bug: every element had `is-visible` hardcoded onto its class list *alongside* the `v-reveal` directive, which meant the element was already visible before the scroll observer ever fired — the entrance transition never actually played. Replaced with `v-reveal-mount` and a proper sequence: image scale-settle → atmosphere fade → eyebrow → masked heading rise → description → CTA, each staggered. Scroll indicator changed from Tailwind's `animate-bounce` to a slow, quiet custom drift (bounce reads as playful, not cinematic). The Builder canvas (`mode="edit"`) skips the full sequence entirely — a compact static rendering instead, so the section stays fast to edit.
- `resources/js/PageBuilder/sections/RestaurantHeroSection.vue` — same treatment, for consistency with the standalone Hero (previously used scroll-triggered `v-reveal` for above-the-fold content, unreliable timing).
- `resources/js/Pages/Guest/Facilities/Show.vue`, `Offers/Show.vue`, `Events/Show.vue`, `Experiences/Show.vue` — **rewritten**. These four had zero motion at all — correct typography/color tokens, but content just appeared instantly with no hero entrance, no scroll reveal on body content. Now match the same sequenced-hero + reveal pattern as everywhere else.
- `routes/web.php` — one stale comment updated (referenced the now-deleted `HomeController`); no behavioral change.

## Verified but NOT changed (already good, don't touch)
- `Events/Index.vue`, `Experiences/Index.vue`, `Offers/Index.vue`, `Restaurants/Index.vue`, `Services/Index.vue`, `Facilities/Index.vue` — all already have correct staggered `v-reveal`.
- `TextSection.vue`, `CtaSection.vue`, `GallerySection.vue` — already well done (editorial split layout, horizontal magazine-style gallery with hover zoom, staggered reveals). No changes made.
- `RestaurantMenuSection.vue` — already has tabbed categories with transitions, dotted price-leaders. No changes made.
- `Restaurants/Show.vue` (the non-customized fallback page) — already mirrors the presentation Hero's visual language well. No changes made.
- `PresentationShow.vue` / `PageView.vue` — correctly generic section loops, no hardcoded layout. No changes needed (each section handles its own motion).

## A second bug found and fixed while verifying the first fix
After adding `v-reveal-mount` to 5 files, I checked whether each file actually imported it — **4 of the 5 new Show-page files did not** (only local per-file imports were added initially, and `v-reveal-mount` wasn't yet a global directive). This would have caused a Vue compiler warning and the directive silently not applying. Fixed by importing it explicitly in each file, **and** by registering it globally in `app.ts` so this entire class of mistake can't recur — matching how `v-reveal` was already handled.

## What I could NOT verify
**No browser is available in this sandbox.** I cannot take screenshots or confirm any of this actually looks right, times correctly, or behaves as described on a real screen, at real viewport sizes, on real network/image-load timing. Everything above is verified by reading the code and reasoning through the Vue/CSS mechanics — not by looking at it. Please view this in your own browser (Home, a Facility/Offer/Event/Experience detail page, and the Restaurant page, at desktop and mobile widths, with reduced-motion on and off) before trusting the timing/feel is right. I'd specifically watch the Hero's staggered entrance on first load and the page-transition speed on a couple of real navigations.
