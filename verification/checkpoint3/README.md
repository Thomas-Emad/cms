# Checkpoint 3 Verification — What Was Actually Run

Same honesty policy as checkpoints 1 and 2: no Composer/Packagist access in
this sandbox, so `tests/Feature/BuilderInteractionPersistenceTest.php`
could **not** be executed via `php artisan test`. Everything below reflects
what was genuinely run.

## Frontend: `verification/frontend/src/PageBuilder/store.test.ts` — real Vitest, real store.ts

```bash
cd verification/frontend
npm install vue@3 @inertiajs/vue3@1 axios vitest
npx vitest run
```

**First run: 21 failures, 2 passes.** This caught a real bug, not a test
artifact: `structuredClone()` cannot clone a Vue `reactive()` Proxy object
in this environment (`DataCloneError: [object Array] could not be cloned`).
Every history-snapshot call and `duplicateSection`'s prop/settings copy
used `structuredClone` directly on reactive state, so almost every
mutating action failed the instant it tried to push a history entry.

**Fix applied:** replaced `structuredClone` with a small `deepClone()`
helper using `JSON.parse(JSON.stringify(...))`. This is arguably more
correct than a fix, not just a workaround — Section props/settings are
JSON by architectural constraint throughout this project (that's literally
what gets persisted to `page_versions.sections`), so a clone that can only
survive JSON-serializable values is the *correct* clone here, not a
limitation.

**After the fix: 25/25 tests passed.** Covering, from the actual (fixed)
`store.ts`:
- add creates a valid unique id (two of the same type get different ids)
- duplicate creates a new id, inserted after the original, selected
- **duplicate deep-copies props/settings** — verified in both directions (editing the clone doesn't touch the original, and vice versa)
- duplicate never carries over resolved `data`
- delete removes the section; deleting the selected section clears selection; deleting a non-selected section leaves selection untouched
- `reorderSections` and `setSectionsOrder` both change the actual array order; `setSectionsOrder` is a genuine no-op (no history entry) when the order is unchanged
- undo restores the previous document state; redo restores the undone state
- **a new edit after undo clears the redo stack** (explicitly asserted via `canRedo`/`history.future`)
- canUndo/canRedo correctly reflect stack state through a full undo→redo cycle
- undo/redo clear an orphaned selection if the selected section no longer exists after the jump
- undo/redo on an empty stack are safe no-ops
- **rapid keystrokes into the same field produce exactly ONE history entry** (simulated typing "Hello" character-by-character within the debounce window)
- typing, pausing past the 500ms debounce window, then typing again produces a **second** entry
- typing into two different fields produces two separate entries, not merged
- a discrete action (addSection) fired mid-text-batch is NOT merged into the batch, and correctly resets the batch window afterward
- **history is capped at exactly 50 entries** after 60 additions
- dirty state starts clean, becomes dirty after any change, and `saveDraft` returns it to clean on success (simulated via a mock that invokes the real `onSuccess` callback `store.ts` registers)
- undo/redo themselves mark the document dirty (verified by saving to clean, then undoing, and confirming dirty flips back to `true`)

## Backend: `verify_interaction_persistence.php`

Same approach as prior checkpoints — replicates `SaveDraftAction`'s exact
logic against real MySQL 8.0.46, confirming the **existing, unmodified**
draft endpoint correctly persists the payload shapes the new interaction
layer produces:

```bash
php verify_interaction_persistence.php
```

**Result: 6/6 checks passed** — reordered array persists in the new order; a duplicated section persists with a distinct id and matching content, and editing the duplicate post-save never affects the original; a deleted section is absent on reload with the remaining section intact.

## What this does NOT cover

- `BuilderInteractionPersistenceTest.php`'s actual HTTP/Inertia execution (same gap as every previous checkpoint).
- Real drag-and-drop interaction (`Canvas.vue`'s `vuedraggable` integration) — untested at the component level; only the `store.setSectionsOrder`/`reorderSections` methods it calls into were tested directly. Mounting `Canvas.vue` with real drag events would need Vue Test Utils + jsdom + simulated pointer events, out of scope for this pass.
- Keyboard shortcut wiring in `Builder.vue` (`Ctrl/Cmd+Z` etc.) — the `undo()`/`redo()` methods it calls are tested; the keydown handler itself isn't.
