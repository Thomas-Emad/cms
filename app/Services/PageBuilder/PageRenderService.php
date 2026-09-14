<?php

namespace App\Services\PageBuilder;

use App\Models\Hotel;

/**
 * Deliberately takes a plain `sections` array rather than a PageVersion -
 * this is what lets the exact same resolution logic serve BOTH standalone
 * Pages and entity-backed presentations, across three callers, without a
 * second rendering architecture:
 *
 *   1. Guest render:         resolveSections($page->publishedVersion->sections, $hotel)
 *   2. Full preview page:    resolveSections($page->draftVersion->sections, $hotel)
 *   3. Live builder preview: resolveSections($unsavedSectionsFromRequest, $hotel)
 *
 * $entity is the optional addition for entity-backed presentations
 * (Restaurant "Customize Guest Page" etc.): when set, it's passed through
 * to every section definition's resolve(), so a `restaurant-info` section
 * can read the CURRENT restaurant's own fields directly rather than
 * needing a query descriptor in props. For standalone Pages, $entity is
 * always null and every existing section behaves exactly as before -
 * nothing about the Page rendering path changed.
 */
class PageRenderService
{
    public function resolveSections(array $sections, Hotel $hotel, mixed $entity = null): array
    {
        return array_map(function (array $section) use ($hotel, $entity) {
            if (! SectionRegistry::has($section['type'])) {
                return [...$section, 'data' => []];
            }

            $definition = SectionRegistry::get($section['type']);

            $data = $definition->resolve($section['props'] ?? [], $hotel, $entity);

            return [...$section, 'data' => $data];
        }, $sections);
    }

    public function resolveOne(array $section, Hotel $hotel, mixed $entity = null): array
    {
        return $this->resolveSections([$section], $hotel, $entity)[0];
    }
}
