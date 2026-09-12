# Checkpoint 1 Verification — What Was Actually Run

## Environment

This sandbox has no `php`/MySQL preinstalled and no access to `packagist.org`
(the network allowlist covers `archive.ubuntu.com`/`security.ubuntu.com` for
apt, and npm/PyPI/crates/GitHub, but not Composer's package registry). That
means **the real Laravel framework cannot be installed here**, so the actual
`php artisan test` run of `tests/Feature/*.php` could not be executed in this
environment — that gap is real and I'm not going to paper over it.

What I *could* do, and did: install real MySQL 8.0.46 and PHP 8.3 via apt
(both reachable), and verify the three riskiest, most novel pieces of this
checkpoint by executing real code against a real MySQL instance rather than
asserting correctness by inspection.

```bash
apt-get install -y mysql-server php-cli php-mysql
service mysql start
mysql -u root -e "CREATE DATABASE grand_horizon_test CHARACTER SET utf8mb4;"
```

## 1. `schema.sql` — the exact migration DDL, translated 1:1

Every `Schema::` / `DB::statement()` call in
`2024_01_03_000001_create_pages_table.php` and
`2024_01_03_000002_create_page_versions_table.php` translated line-for-line
into raw SQL (plus minimal parent tables for FK integrity) and run against
real MySQL 8.0.46:

```bash
mysql -u root grand_horizon_test < schema.sql
```

**Result: applied with zero errors.** `SHOW CREATE TABLE pages` confirms the
`home_marker` column exists exactly as designed:
```
`home_marker` bigint unsigned GENERATED ALWAYS AS (if((`is_home` = 1),`hotel_id`,NULL)) VIRTUAL,
UNIQUE KEY `pages_home_marker_unique` (`home_marker`)
```

Then directly tested the constraint by hand:
- Inserted `is_home=1` for Hotel A → succeeded.
- Inserted a second `is_home=1` row for the **same** Hotel A → **rejected by MySQL**: `ERROR 1062 (23000): Duplicate entry '1' for key 'pages.pages_home_marker_unique'`.
- Inserted `is_home=1` for Hotel B (different hotel) → succeeded (different marker value).
- Inserted three more `is_home=0` rows for Hotel A → all succeeded (NULLs don't collide in a unique index).

This is real MySQL enforcing the constraint, not application code.

## 2. `verify_publish_lifecycle.php`

A PHP+PDO script that issues the **exact same SQL** `CreatePageAction`,
`SaveDraftAction`, and `PublishPageAction` would generate as Eloquent
(insert page + draft version, mutate draft in place, snapshot-and-flip on
publish), run against the real database, asserting every scenario from
`PagePublishLifecycleTest`:

```bash
php verify_publish_lifecycle.php
```

**Result: 15/15 assertions passed** — first publish, draft edit not leaking
into the published row, second publish creating a genuinely new row (not
mutating the first), guest-visible content matching only the published
pointer, and append-only integrity (`draft, published, published` — nothing
deleted or overwritten) across the whole sequence.

## 3. `verify_tenant_isolation.php`

Same approach for `FacilityGridSectionDefinition::resolve()`'s query,
including two hotels' facilities in the same table:

```bash
php verify_tenant_isolation.php
```

**Result: 8/8 assertions passed** — Hotel A's grid never returns Hotel B's
facilities, category/featured_only filters behave correctly, draft-status
facilities never appear, and re-resolving after a prop change produces
correct fresh results (not stale/cached) both ways.

## 4. `verify_section_registry.php`

This one runs the **actual, unmodified** `SectionRegistry.php`,
`HeroSectionDefinition.php`, `TextSectionDefinition.php`, and
`FacilityGridSectionDefinition.php` files copied verbatim from `app/` (not
reimplemented) via a minimal PSR-4 autoloader — these classes have no
Eloquent dependency in their schema/registry methods (only `resolve()`
touches Eloquent, which this script doesn't call). A one-method stub for
`Illuminate\Validation\Rule::in()` was needed since that's a genuine
framework dependency even for declaring the schema array.

```bash
php verify_section_registry.php
```

**Result: 13/13 assertions passed** — known types recognized, unknown
type (`evil-custom-html`) correctly rejected both by `has()` and by `get()`
throwing, and per-type schemas expose the right validation keys.

## What this does NOT cover

- The actual HTTP layer (`Admin\PageController`, `Guest\PageController`,
  `PagePolicy`, Inertia responses) was not executed — that requires the
  full Laravel HTTP kernel, routing, and Inertia adapter, none of which
  can be installed without Composer/Packagist access here.
- `SectionsValidator`'s actual `Validator::make(...)->validate()` call
  (Laravel's real validation engine) was not exercised — only the registry
  lookup and schema-key-shape logic around it.
- These verification scripts are **not** a replacement for
  `php artisan test` — they're a targeted, honest substitute for the parts
  of that suite this sandbox can actually prove, given the constraint that
  Laravel itself isn't installable here. Please run the real PHPUnit suite
  in your own environment (with MySQL, per the earlier warning about the
  `home_marker` migration failing on SQLite) before treating this
  checkpoint as fully verified end-to-end.
