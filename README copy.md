# Grand Horizon Hotel Platform — Full Project (All Phases Merged)

This is every phase and checkpoint built so far, merged into ONE consistent
project tree, in the correct build order. Copy this directly on top of a
fresh Laravel skeleton and follow the setup steps below once.

## What's included

- **Phase 1**: Foundation, multi-tenancy (`hotel_id` + `CurrentHotel` +
  `BelongsToHotel`), minimal auth (login/logout only), admin/guest layouts.
- **Phase 2**: Facilities, Restaurants + Menus, Services, Events, Offers,
  Experiences - full admin CRUD + guest pages, polymorphic `media` table,
  Grand Horizon demo data.
- **Phase 3 (Page Builder)**: `pages`/`page_versions` with draft/publish
  versioning, `SectionRegistry` (backend + frontend), the visual Builder
  (drag-and-drop, duplicate, delete, undo/redo), and all 12 section types
  (hero, text, image, gallery, facility-grid, restaurant-grid,
  service-grid, events, offers, experiences, cta, spacer).

**Note on the last piece (all 12 sections):** this was mid-verification
when consolidated into this zip - the code is written but I had not yet
re-run the backend/frontend test suites against it before packaging this
single-file version. Treat everything through the Component Integration
Verification checkpoint as fully verified; treat the full 12-section
registry as functionally complete but not yet re-confirmed by test
execution in this merged form.

## 1. Create the Laravel skeleton

```bash
composer create-project laravel/laravel grand-horizon
cd grand-horizon
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
npm install
npm install -D @inertiajs/vue3 @vitejs/plugin-vue vue typescript vue-tsc tailwindcss postcss autoprefixer
npm install vuedraggable@^4.1.0
npx tailwindcss init -p
```

## 2. Copy this project's files in

Copy this zip's `app/`, `resources/`, `routes/`, `database/`, `tests/`
folders into your fresh Laravel project, merging with (not replacing)
what the installer generated. `routes/web.php` and `routes/auth.php` here
are **complete, final files** - they replace the generated stubs entirely,
not merge with them.

## 3. Register providers, middleware, and policies

**`bootstrap/providers.php`** (Laravel 11+) — add:
```php
App\Providers\TenancyServiceProvider::class,
```

**`bootstrap/app.php`** — add to `withMiddleware`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'resolve.hotel' => \App\Http\Middleware\ResolveCurrentHotel::class,
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

Also replace the generated `HandleInertiaRequests` middleware stub with
the one in this project (`app/Http/Middleware/HandleInertiaRequests.php`)
— it shares `auth.user` and `hotel` on every Inertia response.

**Policies** — Laravel 11 auto-discovers policies named
`App\Policies\{Model}Policy` by convention, so no manual registration is
strictly required. If you'd rather be explicit (e.g. in an
`AuthServiceProvider`), register:
```php
Hotel::class => HotelPolicy::class,
Facility::class => FacilityPolicy::class,
Restaurant::class => RestaurantPolicy::class,
Service::class => ServicePolicy::class,
Event::class => EventPolicy::class,
Offer::class => OfferPolicy::class,
Experience::class => ExperiencePolicy::class,
Page::class => PagePolicy::class,
```

## 4. Migrate and seed

```bash
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\HotelSeeder
php artisan db:seed --class=Database\\Seeders\\DemoContentSeeder
```

**Important — MySQL required, not SQLite.** The `pages` table uses a
MySQL-specific generated column (`home_marker`) to enforce "one home page
per hotel" at the database level. This will fail on SQLite. Point your
`.env` (and `phpunit.xml` if you run the tests) at a real MySQL database.

Seeded credentials (from `HotelSeeder`):
- `superadmin@platform.example` / `password`
- `admin@grandhorizonhotel.example` / `password`

## 5. Run it

```bash
npm run dev
php artisan serve
```
- Guest site: `http://localhost:8000/` (homepage), `/facilities`,
  `/restaurants`, `/services`, `/events`, `/offers`, `/experiences`
- Admin: `http://localhost:8000/login`, then `/admin/dashboard`
- Page Builder: `/admin/pages/{page}/builder` for any page's `id`
  (the seeded homepage is `page_id = 1` if you seeded fresh)

## 6. Run the tests

I could not execute the PHPUnit suites myself (no Composer/Packagist
access in my sandbox — see each checkpoint's own `verification/README.md`
for exactly what I *did* independently verify against a real MySQL
instance instead). Run the real suite yourself:

```bash
php artisan test
```

Frontend Vitest suites (per Page Builder checkpoint, in
`verification/frontend/` folders included in this zip) — these I *did*
run for real:
```bash
cd verification/frontend    # there are several - one per checkpoint
npm install
npx vitest run
```

## Known open items (carried forward, not yet resolved)

- **Routing decision**: Page Builder pages live at `/pages/{slug}` to
  avoid colliding with `/facilities` etc. See `ROUTING_DECISION_NEEDED.md`
  for the three options — not decided yet, by design.
- **Media picker**: `media_id`/`media_ids` fields in the Builder are raw
  number inputs (no picker UI) — explicitly out of scope so far.
- **No autosave, templates, or device preview yet** — all explicitly
  deferred to a later checkpoint.
