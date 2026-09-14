<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Restaurant;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * Covers both "Restaurant Info" and "Restaurant Features" from the
 * request - Phase 2's Restaurant schema has no dedicated `features`
 * column (only cuisine/dress_code/opening_hours/phone/reservation_url),
 * so a separate Features section would either duplicate this data or
 * require a new migration/field that wasn't asked for. Merged here
 * rather than inventing a features column speculatively; splitting them
 * apart later is a small, additive change once real "feature" data
 * exists (e.g. amenity tags).
 */
class RestaurantInfoSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'restaurant-info';
    }

    public function propsSchema(): array
    {
        return ['show_opening_hours' => ['boolean']];
    }

    public function defaultProps(): array
    {
        return ['show_opening_hours' => true];
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
                'description' => $entity->description,
                'cuisine' => $entity->cuisine,
                'dress_code' => $entity->dress_code,
                'phone' => $entity->phone,
                'opening_hours' => ($props['show_opening_hours'] ?? true) ? $entity->opening_hours : null,
            ],
        ];
    }
}
