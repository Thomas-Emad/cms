<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Restaurant;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class RestaurantLocationSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'restaurant-location';
    }

    public function propsSchema(): array
    {
        return [];
    }

    public function defaultProps(): array
    {
        return [];
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
                'location' => $entity->location,
                'floor' => $entity->floor,
                'phone' => $entity->phone,
            ],
        ];
    }
}
