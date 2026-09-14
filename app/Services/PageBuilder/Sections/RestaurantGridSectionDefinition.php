<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Restaurant;
use App\Services\PageBuilder\Contracts\SectionDefinition;

class RestaurantGridSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'restaurant-grid';
    }

    public function propsSchema(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'cuisine' => ['nullable', 'string', 'max:100'],
            'featured_only' => ['boolean'],
            'limit' => ['integer', 'min:1', 'max:12'],
        ];
    }

    public function defaultProps(): array
    {
        return [
            'title' => 'Dining at Grand Horizon',
            'description' => null,
            'cuisine' => null,
            'featured_only' => false,
            'limit' => 6,
        ];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    /**
     * Follows the exact same scope pattern as Facility (Phase 2's
     * Restaurant model + Guest\RestaurantController) - no new query
     * semantics invented for the Page Builder.
     */
    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        $restaurants = Restaurant::query()
            ->published()
            ->when($props['cuisine'] ?? null, fn ($q, $cuisine) => $q->where('cuisine', $cuisine))
            ->when($props['featured_only'] ?? false, fn ($q) => $q->featured())
            ->with('cover')
            ->ordered()
            ->limit($props['limit'] ?? 6)
            ->get();

        return [
            'restaurants' => $restaurants->map(fn (Restaurant $r) => [
                ...$r->only(['id', 'name', 'slug', 'cuisine', 'location']),
                'cover_image_url' => $r->cover_image_url,
            ])->all(),
        ];
    }
}
