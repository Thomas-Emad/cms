import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { SECTION_REGISTRY } from './registry';
import type { Section } from '@/types/pageBuilder';

/**
 * Generates a stable, unique section id. Not a real ULID/nanoid library -
 * this is intentionally minimal (timestamp + random suffix is plenty
 * collision-resistant for "sections added in one browser session by one
 * admin"), matching the "keep it minimal" instruction for this checkpoint.
 * Swap for a real id library later if multi-client concurrent editing
 * ever makes collision risk non-negligible.
 */
function generateSectionId(type: string): string {
  return `${type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}

export interface BuilderState {
  schema_version: number;
  sections: Section[];
  selectedSectionId: string | null;
  isSaving: boolean;
  isDirty: boolean;
}

/**
 * ONE reactive object is the source of truth for the working document.
 * Builder.vue creates exactly one of these and provides it to
 * ComponentLibrary/Canvas/SettingsPanel via provide/inject - none of them
 * hold their own copy of `sections`, they all read/mutate this one.
 *
 * A composable rather than a Pinia store: Pinia isn't yet a confirmed
 * dependency of this project, and a single per-page-instance reactive
 * object achieves the same "one source of truth" requirement without
 * adding a new library for what is, so far, single-page local state (no
 * cross-component global state is needed yet - if the Builder later needs
 * state shared across routes/instances, that's the point to introduce
 * Pinia for real, not before).
 */
export function usePageBuilderStore(pageId: number, initial: { schema_version: number; sections: Section[] }) {
  const state = reactive<BuilderState>({
    schema_version: initial.schema_version,
    sections: initial.sections,
    selectedSectionId: null,
    isSaving: false,
    isDirty: false,
  });

  const selectedSection = computed(() =>
    state.sections.find((s) => s.id === state.selectedSectionId) ?? null
  );

  function addSection(type: string) {
    const entry = SECTION_REGISTRY[type];
    if (!entry) return;

    const section: Section = {
      id: generateSectionId(type),
      type,
      props: structuredClone(entry.defaultProps),
      settings: {},
    };

    state.sections.push(section);
    state.selectedSectionId = section.id;
    state.isDirty = true;

    if (entry.isDynamic) resolvePreviewFor(section.id);
  }

  function removeSection(id: string) {
    state.sections = state.sections.filter((s) => s.id !== id);
    if (state.selectedSectionId === id) state.selectedSectionId = null;
    state.isDirty = true;
  }

  function selectSection(id: string | null) {
    state.selectedSectionId = id;
  }

  function updateSectionProps(id: string, props: Record<string, unknown>) {
    const section = state.sections.find((s) => s.id === id);
    if (!section) return;

    section.props = { ...section.props, ...props };
    state.isDirty = true;

    const entry = SECTION_REGISTRY[section.type];
    if (entry?.isDynamic) resolvePreviewFor(id);
  }

  function updateSectionSettings(id: string, settings: Record<string, unknown>) {
    const section = state.sections.find((s) => s.id === id);
    if (!section) return;

    section.settings = { ...section.settings, ...settings };
    state.isDirty = true;
  }

  // --- Live in-canvas preview resolution (checkpoint 1 design §2) ---
  // Debounced per-section, so rapid prop edits (typing, dragging a
  // number input) don't fire a request per keystroke. Reuses the exact
  // backend endpoint built in checkpoint 1 - no frontend query logic.
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
        // Re-find the section in case the array changed during the await
        // (e.g. it was removed mid-flight) before writing the result back.
        const current = state.sections.find((s) => s.id === sectionId);
        if (current) current.data = response.data.section.data;
      } catch {
        // Best-effort: a failed live preview shouldn't crash the Builder.
        // The section will just show no data in the canvas until the next
        // successful resolve or a full Preview/Save.
      }
    }, 500);
  }

  // --- Persistence ---
  // Sent shape matches PageSectionsRequest exactly: a flat `sections`
  // array. schema_version is NOT sent to the server - SaveDraftAction
  // always writes schema_version: 1 itself (see checkpoint 1); the
  // client's schema_version here is read-only display/bookkeeping, never
  // authoritative.
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
    selectedSection,
    addSection,
    removeSection,
    selectSection,
    updateSectionProps,
    updateSectionSettings,
    saveDraft,
    publish,
  };
}

export type PageBuilderStore = ReturnType<typeof usePageBuilderStore>;
