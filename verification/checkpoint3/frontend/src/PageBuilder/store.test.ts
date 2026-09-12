import { describe, it, expect, vi, beforeEach } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
  router: { put: vi.fn(), post: vi.fn() },
}));

vi.mock('axios', () => ({
  default: { post: vi.fn(() => Promise.resolve({ data: { section: { data: { facilities: [] } } } })) },
}));

// Copied verbatim from the real registry.ts - see checkpoint 2's
// verification README for why this collaborator is mocked (the real file
// imports .vue SFCs which need a compiler this lightweight setup doesn't
// include). store.ts itself, the code under test, is real and unmodified.
vi.mock('./registry', () => ({
  SECTION_REGISTRY: {
    hero: {
      label: 'Hero',
      defaultProps: { title: '', subtitle: '', media_id: null, button_text: '', button_url: '' },
      isDynamic: false,
      editorFields: [],
    },
    text: {
      label: 'Text',
      defaultProps: { heading: '', body: '' },
      isDynamic: false,
      editorFields: [],
    },
    'facility-grid': {
      label: 'Facility Grid',
      defaultProps: { title: 'Explore Our Facilities', description: null, category: null, featured_only: false, limit: 6, columns: 3 },
      isDynamic: true,
      editorFields: [],
    },
  },
}));

import { usePageBuilderStore } from './store';
import { router } from '@inertiajs/vue3';

describe('usePageBuilderStore — Checkpoint 3 interaction layer (real store.ts)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.useFakeTimers();
  });

  describe('add / duplicate / delete', () => {
    it('add section creates a valid, unique id', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('hero');

      const [a, b] = store.state.sections;
      expect(a.id).toMatch(/^hero-/);
      expect(b.id).toMatch(/^hero-/);
      expect(a.id).not.toBe(b.id);
    });

    it('duplicate creates a section with a NEW id, inserted right after the original', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('text');
      const originalId = store.state.sections[0].id;

      store.duplicateSection(originalId);

      expect(store.state.sections).toHaveLength(2);
      expect(store.state.sections[0].id).toBe(originalId);
      expect(store.state.sections[1].id).not.toBe(originalId);
      expect(store.state.sections[1].type).toBe('text');
      // The duplicate is selected, matching addSection's UX convention.
      expect(store.state.selectedSectionId).toBe(store.state.sections[1].id);
    });

    it('duplicate deep-copies props/settings so editing one never mutates the other', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('text');
      const original = store.state.sections[0];
      store.updateSectionProps(original.id, { heading: 'Original Heading' });
      store.updateSectionSettings(original.id, { background: 'light' });

      store.duplicateSection(original.id);
      const clone = store.state.sections[1];

      // Editing the CLONE must not affect the original.
      store.updateSectionProps(clone.id, { heading: 'Changed On Clone Only' });
      store.updateSectionSettings(clone.id, { background: 'dark' });

      expect(store.state.sections[0].props.heading).toBe('Original Heading');
      expect(store.state.sections[0].settings.background).toBe('light');
      expect(store.state.sections[1].props.heading).toBe('Changed On Clone Only');
      expect(store.state.sections[1].settings.background).toBe('dark');

      // And the reverse: editing the ORIGINAL after duplicating must not
      // retroactively affect the already-created clone.
      store.updateSectionProps(original.id, { heading: 'Original Changed Again' });
      expect(store.state.sections[1].props.heading).toBe('Changed On Clone Only');
    });

    it('duplicate never copies resolved dynamic `data`, only props/settings', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('facility-grid');
      const original = store.state.sections[0];
      original.data = { facilities: [{ id: 1, name: 'Fake cached facility', slug: 'x', category: 'wellness' }] } as any;

      store.duplicateSection(original.id);
      const clone = store.state.sections[1];

      expect(clone.data).toBeUndefined();
    });

    it('delete removes the section from the store', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('text');
      const [hero, text] = store.state.sections;

      store.removeSection(hero.id);

      expect(store.state.sections).toHaveLength(1);
      expect(store.state.sections[0].id).toBe(text.id);
    });

    it('deleting the selected section clears selectedSectionId (no orphaned selection)', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      const id = store.state.sections[0].id;
      store.selectSection(id);

      store.removeSection(id);

      expect(store.state.selectedSectionId).toBeNull();
    });

    it('deleting a NON-selected section leaves the current selection intact', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('text');
      const [hero, text] = store.state.sections;
      store.selectSection(text.id);

      store.removeSection(hero.id);

      expect(store.state.selectedSectionId).toBe(text.id);
    });
  });

  describe('reorder', () => {
    it('reorderSections changes the actual sections array order', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('text');
      store.addSection('facility-grid');
      const [a, b, c] = store.state.sections.map((s) => s.id);

      store.reorderSections(0, 2); // move 'hero' to the end

      expect(store.state.sections.map((s) => s.id)).toEqual([b, c, a]);
    });

    it('setSectionsOrder (the drag-and-drop entry point) applies a full reordered array and marks dirty', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('text');
      const [a, b] = store.state.sections;
      store.state.isDirty = false; // reset to isolate this assertion

      store.setSectionsOrder([b, a]);

      expect(store.state.sections.map((s) => s.id)).toEqual([b.id, a.id]);
      expect(store.state.isDirty).toBe(true);
    });

    it('setSectionsOrder is a no-op (no history entry) when the order is unchanged', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('text');
      const before = store.history.past.length;

      store.setSectionsOrder([...store.state.sections]); // same order, new array reference

      expect(store.history.past.length).toBe(before);
    });
  });

  describe('undo / redo', () => {
    it('undo restores the previous document state', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      expect(store.state.sections).toHaveLength(1);

      store.undo();

      expect(store.state.sections).toHaveLength(0);
    });

    it('redo restores the undone state', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      const id = store.state.sections[0].id;

      store.undo();
      expect(store.state.sections).toHaveLength(0);

      store.redo();

      expect(store.state.sections).toHaveLength(1);
      expect(store.state.sections[0].id).toBe(id);
    });

    it('a new edit after undo clears the redo stack', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      store.addSection('text');

      store.undo(); // undoes 'text' add
      expect(store.canRedo.value).toBe(true);

      store.addSection('facility-grid'); // a genuinely new edit

      expect(store.canRedo.value).toBe(false);
      expect(store.history.future).toHaveLength(0);
    });

    it('canUndo/canRedo reflect stack state correctly through a full cycle', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      expect(store.canUndo.value).toBe(false);
      expect(store.canRedo.value).toBe(false);

      store.addSection('hero');
      expect(store.canUndo.value).toBe(true);
      expect(store.canRedo.value).toBe(false);

      store.undo();
      expect(store.canUndo.value).toBe(false);
      expect(store.canRedo.value).toBe(true);

      store.redo();
      expect(store.canUndo.value).toBe(true);
      expect(store.canRedo.value).toBe(false);
    });

    it('clears an orphaned selection if undo/redo removes the currently selected section', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      const id = store.state.sections[0].id;
      store.selectSection(id);

      store.undo(); // the selected section no longer exists

      expect(store.state.selectedSectionId).toBeNull();
    });

    it('undo/redo on an empty stack is a safe no-op', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      expect(() => store.undo()).not.toThrow();
      expect(() => store.redo()).not.toThrow();
      expect(store.state.sections).toEqual([]);
    });
  });

  describe('meaningful-change grouping (debounced text history)', () => {
    it('rapid keystrokes into the SAME field within the debounce window produce exactly ONE history entry', async () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('text');
      const id = store.state.sections[0].id;
      const before = store.history.past.length;

      // Simulate typing "Hello" - 5 rapid calls, each well within 500ms.
      store.updateSectionPropsBatched(id, { heading: 'H' }, `${id}:heading`);
      await vi.advanceTimersByTimeAsync(50);
      store.updateSectionPropsBatched(id, { heading: 'He' }, `${id}:heading`);
      await vi.advanceTimersByTimeAsync(50);
      store.updateSectionPropsBatched(id, { heading: 'Hel' }, `${id}:heading`);
      await vi.advanceTimersByTimeAsync(50);
      store.updateSectionPropsBatched(id, { heading: 'Hell' }, `${id}:heading`);
      await vi.advanceTimersByTimeAsync(50);
      store.updateSectionPropsBatched(id, { heading: 'Hello' }, `${id}:heading`);

      expect(store.history.past.length).toBe(before + 1);
      expect(store.state.sections[0].props.heading).toBe('Hello');
    });

    it('typing, pausing past the debounce window, then typing again creates a SECOND history entry', async () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('text');
      const id = store.state.sections[0].id;
      const before = store.history.past.length;

      store.updateSectionPropsBatched(id, { heading: 'Hello' }, `${id}:heading`);
      await vi.advanceTimersByTimeAsync(600); // past the 500ms debounce window

      store.updateSectionPropsBatched(id, { heading: 'Hello world' }, `${id}:heading`);

      expect(store.history.past.length).toBe(before + 2);
    });

    it('typing into TWO DIFFERENT fields creates separate history entries, not merged into one', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('text');
      const id = store.state.sections[0].id;
      const before = store.history.past.length;

      store.updateSectionPropsBatched(id, { heading: 'A heading' }, `${id}:heading`);
      store.updateSectionPropsBatched(id, { body: 'Some body text' }, `${id}:body`);

      expect(store.history.past.length).toBe(before + 2);
    });

    it('discrete (non-batched) actions like addSection are NOT merged with an in-progress text batch', async () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('text');
      const id = store.state.sections[0].id;

      store.updateSectionPropsBatched(id, { heading: 'Typing...' }, `${id}:heading`);
      const afterTypingStart = store.history.past.length;

      store.addSection('hero'); // discrete action mid-batch

      expect(store.history.past.length).toBe(afterTypingStart + 1);

      // And the batch window is reset - the next batched call with the
      // SAME key starts a fresh entry rather than silently continuing
      // the old (now-interrupted) batch.
      const afterAdd = store.history.past.length;
      store.updateSectionPropsBatched(id, { heading: 'Typing again' }, `${id}:heading`);
      expect(store.history.past.length).toBe(afterAdd + 1);
    });
  });

  describe('history limit', () => {
    it('caps history.past at 50 entries', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });

      for (let i = 0; i < 60; i++) {
        store.addSection('text');
      }

      expect(store.history.past.length).toBe(50);
    });
  });

  describe('dirty state', () => {
    it('starts clean', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      expect(store.state.isDirty).toBe(false);
    });

    it('becomes dirty after any document change', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      expect(store.state.isDirty).toBe(true);
    });

    it('saveDraft returns the store to a clean state on success', () => {
      // Simulate Inertia's router.put calling the onSuccess callback,
      // exactly as it would after a real successful save round-trip.
      (router.put as any).mockImplementation((_url: string, _payload: unknown, opts: any) => {
        opts.onSuccess?.();
        opts.onFinish?.();
      });

      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');
      expect(store.state.isDirty).toBe(true);

      store.saveDraft();

      expect(store.state.isDirty).toBe(false);
      expect(store.state.isSaving).toBe(false);
    });

    it('undo/redo mark the document dirty too (they are real document changes)', () => {
      const store = usePageBuilderStore(1, { schema_version: 1, sections: [] });
      store.addSection('hero');

      (router.put as any).mockImplementation((_url: string, _payload: unknown, opts: any) => {
        opts.onSuccess?.();
      });
      store.saveDraft();
      expect(store.state.isDirty).toBe(false);

      store.undo();

      expect(store.state.isDirty).toBe(true);
    });
  });
});
