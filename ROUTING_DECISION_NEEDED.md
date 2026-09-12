# Routing Decision Required Before Builder UI Ships

This checkpoint deliberately keeps Page Builder pages under `/pages/{slug}`
to sidestep a real collision, not because that's the intended final URL
scheme. This needs a decision — not a redesign — before the Builder lets
admins create arbitrary-slug pages.

## The collision

Phase 2 already owns `/facilities`, `/restaurants`, `/services`, `/events`,
`/offers`, `/experiences` as guest routes. If an admin names a Page Builder
page with slug `facilities`, and Page Builder pages are served from a bare
`/{slug}` catch-all, one of two things breaks: either the Phase 2 route
wins and the admin's page is unreachable with no clear error, or the
catch-all wins and Phase 2's dedicated controller/guest experience for
facilities silently stops being served.

## Options

1. **Clean URLs (`/{slug}`) with reserved-word validation.** Page creation
   validates the slug against a hardcoded reserved list (`facilities`,
   `restaurants`, `services`, `events`, `offers`, `experiences`, plus
   `admin`, `login`, `logout`) and rejects those slugs with a clear error
   at creation time. Cleanest guest URLs; requires maintaining that list
   as Phase 2-style entities grow, and the list becomes a real constraint
   admins can hit unexpectedly ("why can't I name my page 'events'?").
2. **Keep `/pages/{slug}` permanently** (or a similar fixed prefix). No
   collision possible, ever, by construction — zero reserved-word logic
   needed. Guest URLs are slightly less clean (`/pages/summer-promo`
   instead of `/summer-promo`), which may or may not matter to you
   for a guest-facing hospitality product's URLs.
3. **Bare `/{slug}` catch-all registered LAST, after all Phase 2 static
   routes.** Laravel matches routes in registration order, so Phase 2's
   explicit routes always win over the catch-all, and anything not
   matching those becomes a Page Builder lookup. No reserved-word list
   needed. Risk: a future Phase 2-style feature that wants a clean
   top-level route (e.g. a future `/gallery` entity) would need to be
   registered before the catch-all, or it'll silently be swallowed by
   whatever Page Builder page happens to have that slug — an easy mistake
   to make months from now without a comment flagging it.

## My leaning, not a decision

Option 3 gives the cleanest guest URLs with no admin-facing "reserved
words" surprise, but option 2 is the safest against future
route-registration-order mistakes, and reserved-word validation (option 1)
is a middle ground. This is a product/URL-taste decision as much as a
technical one — flagging it here rather than picking for you.

**Not resolved in this checkpoint. `/pages/{slug}` remains in place until
this is decided**, per your instruction not to redesign routing yet.
