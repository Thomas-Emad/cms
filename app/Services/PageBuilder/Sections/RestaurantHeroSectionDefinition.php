<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Restaurant;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * Entity-backed sections (restaurant-*) differ from content-grid sections
 * (facility-grid etc.) in one key way: there is no query descriptor in
 * props at all, because there's nothing to filter - the Builder is
 * already scoped to exactly one restaurant via $entity. props here are
 * pure presentation overrides (e.g. a custom hero title), never a copy
 * of the restaurant's actual data - the restaurant's real name/
 * description/etc. are read from $entity at resolve() time, every time.
 */
class RestaurantHeroSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'restaurant-hero';
    }

    public function propsSchema(): array
    {
        return [
            'subtitle_override' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function defaultProps(): array
    {
        return ['subtitle_override' => '', 'button_text' => 'Reserve a Table'];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        if (! $entity instanceof Restaurant) {
            return ['restaurant' => null];
        }

        return [
            'restaurant' => [
                'name' => $entity->name,
                'cuisine' => $entity->cuisine,
                'subtitle' => $props['subtitle_override'] ?: $entity->cuisine,
                'cover_image_url' => $entity->cover_image_url,
                'button_text' => $props['button_text'] ?? 'Reserve a Table',
                'reservation_url' => $entity->reservation_url,
            ],
        ];
    }
}
