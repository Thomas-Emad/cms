# Component Integration Verification — What Was Actually Run

## Setup

Unlike checkpoints 1-3 (which unit-tested `store.ts` in isolation with
`./registry` mocked, since mounting real `.vue` files needs an SFC
compiler), this checkpoint installed the full component-testing stack and
used the **actual, unmodified** `.vue` files — no mocked registry, no
mocked store:

```bash
npm install vue@3 @inertiajs/vue3@1 axios vitest \
  @vue/test-utils jsdom @vitejs/plugin-vue vuedraggable@^4.1.0
```

`vitest.config.ts` uses the real `@vitejs/plugin-vue` (compiles the actual
SFCs) and `environment: 'jsdom'` (so components genuinely mount into a DOM,
not just run in Node).

Real files used, copied verbatim from the project, zero reimplementation:
`store.ts`, `registry.ts`, `Canvas.vue`, `SettingsPanel.vue`,
`ComponentLibrary.vue`, `SectionRenderer.vue`, all three section
components (`HeroSection.vue`, `TextSection.vue`,
`FacilityGridSection.vue`), and `FacilityCard.vue` (Phase 2's real card
component, since `FacilityGridSection.vue` renders it - not stubbed).

**Mocked, as explicitly permitted:** `@inertiajs/vue3`'s `router` (would
otherwise attempt real navigation) and `Link` (needs a live Inertia page
context to resolve `href` normally; a plain `<a>` stand-in is enough for
`FacilityCard.vue` to mount) — both external runtime dependencies, not
project code. `axios` is mocked the same way it was in every prior
checkpoint (no real network calls in a unit/component test).

## Command executed

```bash
cd verification/frontend
npx vitest run
```

## Actual result

```
✓ src/PageBuilder/Canvas.test.ts (8 tests)
✓ src/PageBuilder/SettingsPanel.test.ts (9 tests)
✓ src/PageBuilder/ComponentLibrary.test.ts (5 tests)

Test Files  3 passed (3)
     Tests  22 passed (22)
```

**All 22 tests passed on the real, unmodified components.** No bug was
found this round — worth stating plainly rather than manufacturing a
finding: checkpoint 3's real Vitest run caught a genuine
`structuredClone`/Proxy bug; this pass exercised the actual `.vue`
component wiring on top of that already-fixed `store.ts` and it held up
correctly the first time. A clean result is still a real result, not an
absence of testing.

## Component coverage achieved

**Canvas.vue** (8 tests):
- Renders sections read directly from the real store
- Clicking a section sets `store.state.selectedSectionId`
- The selected section's wrapper gets the `border-blue-400` class; unselected siblings don't
- The Remove button calls `store.removeSection(id)` — verified both via spy and via the real resulting `state.sections` length
- The Duplicate button calls `store.duplicateSection(id)` — verified both via spy and real resulting array
- **Drag/reorder integration boundary** (see honesty note below): triggering the draggable child's `update:modelValue` event (the same event vuedraggable emits on a real drop) is confirmed to reach `store.setSectionsOrder` via Canvas's actual computed v-model wiring, and the real store order changes as a result
- The draggable child's bound list is confirmed to be the real `store.state.sections` reference (length/content match), not a separate copy
- Sections added to the store *externally* (simulating another component's action) appear in Canvas without remounting — confirming Canvas holds no stale local copy

**SettingsPanel.vue** (9 tests):
- Shows the placeholder when nothing is selected
- Displays the real selected section's label and current prop values
- A text field edit calls `updateSectionPropsBatched` (not `updateSectionProps`)
- A toggle edit calls `updateSectionProps` (not the batched variant)
- A select edit calls `updateSectionProps`
- Edits are confirmed to reach real `store.state.sections[...].props`, not just the spy
- Switching the selected section updates the displayed field values and label
- Selecting a nonexistent id doesn't throw and falls back to the placeholder
- **Integration confirmation of checkpoint 3's batching, exercised through the real component this time**: typing three characters via `setValue()` still produces exactly one history entry

**ComponentLibrary.vue** (5 tests):
- Rendered buttons match `Object.keys(SECTION_REGISTRY)` exactly (label-checked, not just counted)
- Clicking a button adds a section through the real store
- Two clicks of the same type produce two distinct ids
- The added section's `props` deep-equal the registry's `defaultProps`, `settings` is `{}`
- Clicking two different types in sequence adds each with its own correct type and defaults

## Drag-and-drop: what is and isn't covered (honesty note, as instructed)

**Not tested:** real SortableJS pointer-drag physics, ghost-element
rendering, drag-start/drag-over/drag-end DOM mechanics. Simulating genuine
mouse-capture drag sequences in jsdom is unreliable — jsdom doesn't
implement the drag-and-drop or pointer-capture APIs SortableJS depends on,
so a test claiming to do this would either not actually exercise
SortableJS's real code path or would be too brittle to trust.

**What IS tested, without faking the result:** the exact boundary
Canvas.vue owns — its `sections` computed's get/set, which is what
vuedraggable calls via `update:modelValue` the instant a real drop
completes. The test triggers that same event on the real mounted
`draggable` child component and asserts it reaches the real
`store.setSectionsOrder`, and that the store's actual array reorders. This
is the correct scope boundary: Canvas.vue's integration code is verified
directly; SortableJS's own internals (a third-party library) are not
re-tested, which wouldn't be this project's responsibility to verify
anyway.

## Remaining gaps

- Real pointer-drag simulation, as explained above — would need a
  browser-based test runner (Playwright/Cypress component testing) rather
  than jsdom if this is ever considered worth closing.
- `Builder.vue`'s keyboard shortcut handler (`Ctrl/Cmd+Z` etc.) still
  isn't component-tested — only `store.undo()`/`redo()` themselves are
  (checkpoint 3). Mounting `Builder.vue` itself wasn't in this
  checkpoint's scope (Canvas/SettingsPanel/ComponentLibrary only).
- Backend (PHPUnit) execution remains blocked by the same
  Composer/Packagist sandbox limitation noted in every previous
  checkpoint — not applicable to this checkpoint's scope anyway, since it
  was frontend-component-only by your instruction.
