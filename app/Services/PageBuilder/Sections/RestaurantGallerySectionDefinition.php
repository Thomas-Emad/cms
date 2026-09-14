<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Restaurant;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * Unlike the standalone `gallery` section (which stores an admin-chosen
 * media_ids list in props, since it has no owning entity), this section
 * has nothing to choose - it always shows the CURRENT restaurant's own
 * gallery (Phase 2's HasMedia trait), managed from the Restaurant editor
 * itself, not from inside the Page Builder. props only control display.
 */
class RestaurantGallerySectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'restaurant-gallery';
    }

    public function propsSchema(): array
    {
        return ['title' => ['nullable', 'string', 'max:255']];
    }

    public function defaultProps(): array
    {
        return ['title' => 'Gallery'];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        if (! $entity instanceof Restaurant) {
            return ['media' => []];
        }

        return [
            'media' => $entity->gallery->map(fn ($m) => ['url' => $m->url, 'alt_text' => $m->alt_text])->all(),
        ];
    }
}
