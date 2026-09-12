Checkpoint 3 introduces one new frontend dependency, used only by
Canvas.vue's drag-and-drop:

    npm install vuedraggable@^4.1.0

vuedraggable@4 wraps SortableJS and is the maintained Vue 3-compatible
fork (the original `vuedraggable` v2 line is Vue 2 only). No other new
dependencies were added this checkpoint - store.ts's history/duplicate/
reorder logic is plain Vue reactivity, no new libraries.
