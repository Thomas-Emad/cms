<?php

namespace App\Services\PageBuilder\Sections;

use App\Models\Hotel;
use App\Models\Restaurant;
use App\Services\PageBuilder\Contracts\SectionDefinition;

/**
 * THIS is the section the entire entity-presentation architecture exists
 * to support correctly: it resolves current restaurant -> current active
 * menu -> categories -> items, fresh, every single render. If the
 * restaurant's menu is edited in the Menu Editor after this page was
 * published, the guest page reflects that instantly on next load -
 * because nothing about the menu is ever copied into page JSON, only
 * "show this restaurant's menu" is stored (which, for an entity-backed
 * presentation, is implicit - there isn't even a query descriptor prop,
 * since there's exactly one menu that could possibly be meant).
 */
class RestaurantMenuSectionDefinition implements SectionDefinition
{
    public function type(): string
    {
        return 'restaurant-menu';
    }

    public function propsSchema(): array
    {
        return ['title' => ['nullable', 'string', 'max:255']];
    }

    public function defaultProps(): array
    {
        return ['title' => 'Menu'];
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function resolve(array $props, Hotel $hotel, mixed $entity = null): array
    {
        if (! $entity instanceof Restaurant) {
            return ['menu' => null];
        }

        $menu = $entity->activeMenu()->with('categories.items')->first();

        if (! $menu) {
            return ['menu' => null];
        }

        return [
            'menu' => [
                'categories' => $menu->categories->map(fn ($category) => [
                    'name' => $category->name,
                    'items' => $category->items
                        ->where('is_available', true)
                        ->map(fn ($item) => [
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => $item->price,
                            'dietary_info' => $item->dietary_info,
                        ])->values(),
                ]),
            ],
        ];
    }
}
