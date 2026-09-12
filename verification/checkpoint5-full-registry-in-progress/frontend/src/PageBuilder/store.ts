import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { SECTION_REGISTRY } from './registry';
import type { Section } from '@/types/pageBuilder';

function generateSectionId(type: string): string {
  return `${type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}

/**
 * Deep-clones plain JSON-shaped data. Used instead of `structuredClone`
 * because `state.sections` (and any section's `props`/`settings`) are
 * Vue reactive Proxy objects, and structuredClone cannot clone a Proxy in
 * all JS environments (confirmed failing under Node/Vitest during this
 * checkpoint's verification - see verification/README.md). JSON
 * round-tripping is also more semantically honest here: Section data is
 * JSON by architectural constraint throughout this project (it's what
 * gets persisted as page_versions.sections), so a clone that only
 * survives JSON-serializable values is the correct clone, not a
 * limitation - anything a real structuredClone could preserve that JSON
 * can't (functions, Dates, Maps, etc.) should never appear in section
 * props/settings in the first place.
 */
function deepClone<T>(value: T): T {
  return JSON.parse(JSON.stringify(value));
}

function sameOrder(a: Section[], b: Section[]): boolean {
  return a.length === b.length && a.every((s, i) => s.id === b[i].id);
}

const HISTORY_LIMIT = 50;
const HISTORY_DEBOUNCE_MS = 500;

export interface BuilderState {
  schema_version: number;
  sections: Section[];
  selectedSectionId: string | null;
  isSaving: boolean;
  isDirty: boolean;
}

/**
 * ONE reactive object is the source of truth for the working document -
 * unchanged from checkpoint 2. This checkpoint adds interaction methods
 * (duplicate, reorder, undo/redo) on top of it; none of them introduce a
 * second copy of `sections` anywhere. Undo/redo's `history.past`/`future`
 * hold independent snapshots (deep clones) precisely so they DON'T alias
 * the live `state.sections` array - restoring a snapshot replaces
 * `state.sections` wholesale rather than patching it in place.
 */
export function usePageBuilderStore(pageId: number, initial: { schema_version: number; sections: Section[] }) {
  const state = reactive<BuilderState>({
    schema_version: initial.schema_version,
    sections: initial.sections,
    selectedSectionId: null,
    isSaving: false,
    isDirty: false,
  });

  // History is intentionally NOT persisted anywhere (not in the store's
  // saved payload, not sent to the server) - it's pure in-memory editing
  // convenience for this browser session, per the checkpoint's scope.
  const history = reactive<{ past: Section[][]; future: Section[][] }>({
    past: [],
    future: [],
  });

  const canUndo = computed(() => history.past.length > 0);
  const canRedo = computed(() => history.future.length > 0);

  const selectedSection = computed(() =>
    state.sections.find((s) => s.id === state.selectedSectionId) ?? null
  );

  // --- History bookkeeping -----------------------------------------
  //
  // `activeBatchKey` identifies an in-progress "meaningful change" group
  // (e.g. "section-3:title" while the admin is mid-sentence typing a
  // title). While a batch is active, further calls with the SAME key
  // extend the batch's debounce window instead of pushing a new
  // snapshot - this is what keeps "type a sentence" to one history entry
  // instead of one per keystroke. A call with a DIFFERENT key (or
  // `null`, used by discrete actions like add/remove/duplicate/reorder)
  // always pushes a fresh snapshot of the state as it was BEFORE this
  // change, and always clears the redo stack - satisfying "new edit
  // after undo clears redo".
  let activeBatchKey: string | null = null;
  let activeBatchTimer: ReturnType<typeof setTimeout> | null = null;

  function snapshotBeforeChange(batchKey: string | null) {
    if (batchKey !== null && batchKey === activeBatchKey) {
      // Same field, still typing - extend the window, don't snapshot again.
      if (activeBatchTimer) clearTimeout(activeBatchTimer);
      activeBatchTimer = setTimeout(() => { activeBatchKey = null; }, HISTORY_DEBOUNCE_MS);
      return;
    }

    // A genuinely new change: snapshot the CURRENT (pre-change) sections.
    history.past.push(deepClone(state.sections));
    if (history.past.length > HISTORY_LIMIT) history.past.shift();
    history.future.length = 0; // redo stack is invalidated by any new edit

    if (activeBatchTimer) clearTimeout(activeBatchTimer);
    if (batchKey !== null) {
      activeBatchKey = batchKey;
      activeBatchTimer = setTimeout(() => { activeBatchKey = null; }, HISTORY_DEBOUNCE_MS);
    } else {
      activeBatchKey = null;
    }
  }

  function undo() {
    if (!history.past.length) return;
    const previous = history.past.pop()!;
    history.future.push(deepClone(state.sections));
    if (history.future.length > HISTORY_LIMIT) history.future.shift();

    state.sections = previous;
    if (!state.sections.some((s) => s.id === state.selectedSectionId)) {
      state.selectedSectionId = null;
    }
    state.isDirty = true;
    activeBatchKey = null;
  }

  function redo() {
    if (!history.future.length) return;
    const next = history.future.pop()!;
    history.past.push(deepClone(state.sections));
    if (history.past.length > HISTORY_LIMIT) history.past.shift();

    state.sections = next;
    if (!state.sections.some((s) => s.id === state.selectedSectionId)) {
      state.selectedSectionId = null;
    }
    state.isDirty = true;
    activeBatchKey = null;
  }

  // --- Document mutations --------------------------------------------

  function addSection(type: string) {
    const entry = SECTION_REGISTRY[type];
    if (!entry) return;

    snapshotBeforeChange(null);

    const section: Section = {
      id: generateSectionId(type),
      type,
      props: deepClone(entry.defaultProps),
      settings: deepClone(entry.defaultSettings ?? {}),
    };

    state.sections.push(section);
    state.selectedSectionId = section.id;
    state.isDirty = true;

    if (entry.isDynamic) resolvePreviewFor(section.id);
  }

  function removeSection(id: string) {
    if (!state.sections.some((s) => s.id === id)) return;

    snapshotBeforeChange(null);

    state.sections = state.sections.filter((s) => s.id !== id);
    if (state.selectedSectionId === id) state.selectedSectionId = null;
    state.isDirty = true;
  }

  /**
   * Deep-copies props/settings so later edits to either section never
   * affect the other - `deepClone` (JSON-based) is used specifically (not a
   * shallow spread) because props can themselves contain nested objects/
   * arrays (e.g. a future gallery section's `media_ids` array).
   */
  function duplicateSection(id: string) {
    const index = state.sections.findIndex((s) => s.id === id);
    if (index === -1) return;

    snapshotBeforeChange(null);

    const original = state.sections[index];
    const clone: Section = {
      id: generateSectionId(original.type),
      type: original.type,
      props: deepClone(original.props),
      settings: deepClone(original.settings),
      // Deliberately no `data` key on the clone - resolved dynamic data
      // is never copied, only the query descriptor (props) is. A fresh
      // resolve is triggered below for dynamic types instead.
    };

    state.sections.splice(index + 1, 0, clone);
    state.selectedSectionId = clone.id;
    state.isDirty = true;

    const entry = SECTION_REGISTRY[clone.type];
    if (entry?.isDynamic) resolvePreviewFor(clone.id);
  }

  function selectSection(id: string | null) {
    state.selectedSectionId = id;
  }

  /**
   * Immediate-history variant: used for discrete, already-atomic edits
   * (toggle, select, number field) where every change IS a meaningful
   * change - no debounce/batching needed because there's no "typing a
   * sentence" concern for a single click.
   */
  function updateSectionProps(id: string, props: Record<string, unknown>) {
    applyPropsChange(id, props, null);
  }

  /**
   * Debounced-history variant: used for free-text fields (text/textarea/
   * number-while-typing) where each keystroke calls this with the SAME
   * batchKey (conventionally `${sectionId}:${fieldKey}`) - only the first
   * keystroke in a burst creates a history entry; the rest just update
   * the batch's debounce window.
   */
  function updateSectionPropsBatched(id: string, props: Record<string, unknown>, batchKey: string) {
    applyPropsChange(id, props, batchKey);
  }

  function applyPropsChange(id: string, props: Record<string, unknown>, batchKey: string | null) {
    const section = state.sections.find((s) => s.id === id);
    if (!section) return;

    snapshotBeforeChange(batchKey);

    section.props = { ...section.props, ...props };
    state.isDirty = true;

    const entry = SECTION_REGISTRY[section.type];
    if (entry?.isDynamic) resolvePreviewFor(id);
  }

  function updateSectionSettings(id: string, settings: Record<string, unknown>) {
    const section = state.sections.find((s) => s.id === id);
    if (!section) return;

    snapshotBeforeChange(null);

    section.settings = { ...section.settings, ...settings };
    state.isDirty = true;
  }

  /**
   * Canonical reorder entry point - both the drag-and-drop UI and tests
   * call this same function, so there is exactly one code path that
   * mutates section order (see Canvas.vue's v-model getter/setter, which
   * calls this via setSectionsOrder rather than letting the drag library
   * mutate the array by itself).
   */
  function reorderSections(fromIndex: number, toIndex: number) {
    if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) return;
    if (fromIndex >= state.sections.length || toIndex >= state.sections.length) return;

    snapshotBeforeChange(null);

    const updated = [...state.sections];
    const [moved] = updated.splice(fromIndex, 1);
    updated.splice(toIndex, 0, moved);
    state.sections = updated;
    state.isDirty = true;
  }

  /**
   * Used by Canvas.vue's draggable v-model setter, which receives the
   * ALREADY-reordered array from the drag library after a drop. A no-op
   * guard skips history pollution when a drag ends without actually
   * changing order (e.g. picked up and dropped back in place).
   */
  function setSectionsOrder(newSections: Section[]) {
    if (sameOrder(state.sections, newSections)) return;

    snapshotBeforeChange(null);

    state.sections = newSections;
    state.isDirty = true;
  }

  // --- Live in-canvas preview resolution (unchanged from checkpoint 2) ---
  const debounceTimers: Record<string, ReturnType<typeof setTimeout>> = {};

  function resolvePreviewFor(sectionId: string) {
    clearTimeout(debounceTimers[sectionId]);
    debounceTimers[sectionId] = setTimeout(async () => {
      const section = state.sections.find((s) => s.id === sectionId);
      if (!section) return;

      try {
        const response = await axios.post(`/admin/pages/${pageId}/resolve-preview`, {
          sections: [{ id: section.id, type: section.type, props: section.props, settings: section.settings }],
        });
        const current = state.sections.find((s) => s.id === sectionId);
        if (current) current.data = response.data.section.data;
      } catch {
        // Best-effort, as in checkpoint 2.
      }
    }, 500);
  }

  // --- Persistence (unchanged from checkpoint 2) ---
  function saveDraft() {
    state.isSaving = true;
    router.put(
      `/admin/pages/${pageId}/draft`,
      {
        sections: state.sections.map((s) => ({
          id: s.id, type: s.type, props: s.props, settings: s.settings,
        })),
      },
      {
        preserveScroll: true,
        onSuccess: () => { state.isDirty = false; },
        onFinish: () => { state.isSaving = false; },
      }
    );
  }

  function publish() {
    router.post(`/admin/pages/${pageId}/publish`, {}, { preserveScroll: true });
  }

  return {
    state,
    history,
    canUndo,
    canRedo,
    selectedSection,
    addSection,
    removeSection,
    duplicateSection,
    selectSection,
    updateSectionProps,
    updateSectionPropsBatched,
    updateSectionSettings,
    reorderSections,
    setSectionsOrder,
    undo,
    redo,
    saveDraft,
    publish,
  };
}

export type PageBuilderStore = ReturnType<typeof usePageBuilderStore>;
