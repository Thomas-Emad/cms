<?php

namespace App\Services\PageBuilder;

use App\Models\Hotel;

/**
 * Deliberately takes a plain `sections` array rather than a PageVersion -
 * this is what lets the exact same resolution logic serve three different
 * callers without a separate rendering architecture for each:
 *
 *   1. Guest render:        resolveSections($page->publishedVersion->sections, $hotel)
 *   2. Full preview page:   resolveSections($page->draftVersion->sections, $hotel)
 *   3. Live builder preview: resolveSections($unsavedSectionsFromRequest, $hotel)
 *
 * Case 3 is the one that answers "the builder must not depend on stale
 * resolved data" - the admin's in-memory (not-yet-saved) props go straight
 * into this same function via a lightweight endpoint, so a category/limit
 * change is reflected by a real fresh query, not a cached/stale value.
 */
class PageRenderService
{
    /**
     * @param array $sections Array of ['id' => ..., 'type' => ..., 'props' => ..., 'settings' => ...]
     * @return array Same shape, each section additionally carrying a 'data' key.
     */
    public function resolveSections(array $sections, Hotel $hotel): array
    {
        return array_map(function (array $section) use ($hotel) {
            if (! SectionRegistry::has($section['type'])) {
                // A section type that existed when this page was saved but
                // has since been removed from the registry. Render nothing
                // for it rather than throwing - a page shouldn't 500
                // because one section type was deprecated.
                return [...$section, 'data' => []];
            }

            $definition = SectionRegistry::get($section['type']);

            $data = $definition->resolve($section['props'] ?? [], $hotel);

            return [...$section, 'data' => $data];
        }, $sections);
    }

    /**
     * Resolve a single section by its current id within a larger sections
     * array - used by the live in-canvas preview endpoint, which only
     * wants to re-resolve the one dynamic section the admin just edited,
     * not the whole page, on every debounced prop change.
     */
    public function resolveOne(array $section, Hotel $hotel): array
    {
        return $this->resolveSections([$section], $hotel)[0];
    }
}
