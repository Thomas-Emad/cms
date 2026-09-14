# Entity-Backed Pages — What Was Actually Run

Same honesty policy as every checkpoint before this one.

## No browser available — stated directly

This sandbox has no browser automation tool. The requested manual
click-through (Login → Restaurants → Open → Edit → Menu → Save →
Customize Guest Page → Builder → add sections → Save Draft → Preview →
Publish → guest page) was **NOT performed in an actual running browser**.
I cannot claim otherwise. What follows is real code, genuinely executed,
against real MySQL and real Vitest+jsdom — which is meaningful evidence,
but it is not the same as watching the whole stack run together.

## Backend: real MySQL 8.0.46

```bash
php verify_entity_presentation.php
```

**6/6 checks passed**: `firstOrCreate` is idempotent (clicking "Customize
Guest Page" twice never creates a duplicate); the database's own
`unique(presentable_type, presentable_id)` constraint independently
prevents a second presentation for the same entity; tenant isolation
(Hotel A cannot find Hotel B's restaurant's presentation under its own
`hotel_id`); the draft/publish snapshot-and-pointer-flip mechanics are
byte-for-byte the same pattern as `Page`/`PageVersion`; and the
**architecturally critical check** — the published JSON contains zero
menu items, only a reference to "show this restaurant's menu," confirming
menu data is never copied into page JSON.

## Frontend: real Vitest, real `store.ts`, real `registry.ts` with real Vue SFC compilation

```bash
cd verification/frontend
npm install vue@3 @inertiajs/vue3@1 axios vitest @vitejs/plugin-vue jsdom
npx vitest run
```

```
✓ src/PageBuilder/store.test.ts    (3 tests)
✓ src/PageBuilder/registry.test.ts (5 tests)

Test Files  2 passed (2)
     Tests  8 passed (8)
```

`store.test.ts` proves the SAME store code posts to Page URLs when given
a Page-shaped `apiBase` and to Restaurant-presentation URLs when given a
Restaurant-shaped one — zero duplicated logic, confirmed by execution, not
just by reading the code. `registry.test.ts` imports the real
`registry.ts` with real `@vitejs/plugin-vue` compiling all 17 real
section components (not mocked, unlike `store.ts`'s tests, which mock
`./registry` for the reason documented in every prior checkpoint) and
confirms context filtering behaves correctly in both directions.

## What this does NOT cover

- **The actual browser flow** — the single most important gap. Please run
  it yourself before trusting this is wired end-to-end.
- `EntityPresentationTest.php`'s HTTP/Inertia execution — same
  Composer/Packagist gap as every prior checkpoint.
- Component-level (mount/render) tests for the 5 new Vue section
  components themselves, or for `ComponentLibrary.vue`'s context-prop
  rendering — only the underlying `sectionsForContext()` logic was
  verified, not the component consuming it. Given the scope of this
  request, this is a real gap, not an oversight I'm hiding.
- **No visual/UI polish work was done this pass** — see the final report
  for what was explicitly deferred (shadcn-vue tabs, redesigned entity
  editors, drag-drop menu UI, confirmation dialogs, skeletons, sidebar
  icons). The architecture request was prioritized as the harder,
  higher-risk, more foundational piece.
