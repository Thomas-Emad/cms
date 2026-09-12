# Checkpoint 2 Verification — What Was Actually Run

Same honesty policy as checkpoint 1: this sandbox still cannot install the
real Laravel framework (no Composer/Packagist access), so
`tests/Feature/BuilderPlumbingTest.php` was **not** executed end-to-end via
`php artisan test`. What follows is what was genuinely executed.

## Backend: `verify_builder_plumbing.php`

Same approach as checkpoint 1 — replicates the exact SQL/logic
`Admin\PageController::edit()`/`preview()` and `PagePolicy::update()` would
produce, run against real MySQL 8.0.46:

```bash
php verify_builder_plumbing.php
```

**Result: 9/9 checks passed**, covering:
- Builder loads the draft version, and continues to after a publish happens (never accidentally reads the published snapshot)
- Multi-section save preserves exact structure on reload, including nested prop values and correct boolean typing (not stringified)
- Tenant isolation: Hotel A's admin can act on their own page, cannot act on Hotel B's page
- Preview resolves `facility-grid` using the latest **saved** props after an edit — not the first preview's cached result

## Frontend: `frontend/src/PageBuilder/store.test.ts` — genuinely executed via real Vitest

This is the one that goes further than checkpoint 1: `npm`/`node` **are**
available in this sandbox and `registry.npmjs.org` is in the network
allowlist, so real `vue`, `@inertiajs/vue3`, `axios`, and `vitest` were
installed and the **actual, unmodified `store.ts`** was tested for real:

```bash
cd verification/frontend
npm install vue@3 @inertiajs/vue3@1 axios vitest
npx vitest run
```

**Result: 10/10 tests passed**, actually executed, not simulated:
- `addSection` uses registry `defaultProps` and generates unique ids (two sections of the same type get different ids)
- `removeSection` removes only the target and clears selection correctly
- `updateSectionProps` merges without clobbering untouched keys
- **A dynamic section's (`facility-grid`) prop edit triggers a debounced call to `/admin/pages/{id}/resolve-preview`** — verified via fake timers, confirming the debounce actually waits
- **Rapid successive edits produce exactly ONE resolve call, carrying the LATEST prop value** — directly verifies the "must not depend on stale resolved data" requirement at the frontend layer
- A static section (`hero`) edit **never** triggers a resolve call — confirms `isDynamic` correctly gates network calls
- `saveDraft`'s payload is exactly `{sections: [{id, type, props, settings}]}` — explicitly asserted that resolved `data` and `schema_version` are **not** in the outgoing payload, directly verifying "do not store resolved dynamic content in the page JSON"
- `publish` posts to the correct endpoint

`./registry` was mocked (values copied verbatim from the real `registry.ts`) only because the real file imports `.vue` Single File Components for Vue rendering, which needs a full SFC compiler this lightweight setup doesn't include — `@inertiajs/vue3` and `axios` were mocked at the IO boundary (no real network calls should happen in a unit test). The code under test — `store.ts` itself — is the real, unmodified production file.

## What this does NOT cover

- The actual HTTP/Inertia round trip (`BuilderPlumbingTest.php`'s `actingAs()->get()/put()` calls) — needs the full Laravel HTTP kernel and Inertia server-side adapter, neither installable here.
- Vue component rendering/mounting (`Canvas.vue`, `SettingsPanel.vue`, `ComponentLibrary.vue`) — no Vue Test Utils / jsdom setup was built for this checkpoint; only the framework-agnostic store logic was exercised.
- `Illuminate\Validation`'s real rule engine (same gap noted in checkpoint 1, still unaddressed, still acceptable per your last message).

Please run `php artisan test` and, ideally, a real `npm run test` in the actual project (with the true `registry.ts` and a jsdom/Vue Test Utils setup for component-level tests) before treating this checkpoint as fully verified.
