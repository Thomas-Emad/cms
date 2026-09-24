<?php

namespace App\Actions\Restaurants;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class UpdateMenuAction
{
    /**
     * @param  array<int, array{id?: int, name: string, items: array<int, array>}>  $categories
     */
    public function execute(Restaurant $restaurant, array $categories): Menu
    {
        return DB::transaction(function () use ($restaurant, $categories) {
            $menu = $restaurant->activeMenu()->first()
                ?? $restaurant->menus()->create(['name' => 'Main Menu', 'is_active' => true]);

            $keepCategoryIds = [];

            foreach ($categories as $categoryIndex => $categoryData) {
                $category = isset($categoryData['id'])
                    ? MenuCategory::where('menu_id', $menu->id)->find($categoryData['id'])
                    : null;

                $category = $category ?? new MenuCategory(['menu_id' => $menu->id]);
                $category->fill([
                    'name' => $categoryData['name'],
                    'sort_order' => $categoryIndex,
                ])->save();

                if (! empty($categoryData['translations']['ar'])) {
                    $category->setTranslations('ar', $categoryData['translations']['ar']);
                } elseif (! empty($categoryData['name_ar'])) {
                    $category->setTranslations('ar', ['name' => trim($categoryData['name_ar'])]);
                }

                $keepCategoryIds[] = $category->id;

                $keepItemIds = [];

                foreach ($categoryData['items'] ?? [] as $itemIndex => $itemData) {
                    $item = isset($itemData['id'])
                        ? MenuItem::where('menu_category_id', $category->id)->find($itemData['id'])
                        : null;

                    $item = $item ?? new MenuItem(['menu_category_id' => $category->id]);
                    $item->fill([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'price' => $itemData['price'],
                        'dietary_info' => $itemData['dietary_info'] ?? [],
                        'is_available' => $itemData['is_available'] ?? true,
                        'sort_order' => $itemIndex,
                    ])->save();

                    if (! empty($itemData['translations']['ar'])) {
                        $item->setTranslations('ar', $itemData['translations']['ar']);
                    } else {
                        $arItemData = [];
                        if (! empty($itemData['name_ar'])) {
                            $arItemData['name'] = trim($itemData['name_ar']);
                        }
                        if (! empty($itemData['description_ar'])) {
                            $arItemData['description'] = trim($itemData['description_ar']);
                        }
                        if (! empty($arItemData)) {
                            $item->setTranslations('ar', $arItemData);
                        }
                    }

                    $keepItemIds[] = $item->id;
                }

                // Remove items that were dropped from this category on save.
                MenuItem::where('menu_category_id', $category->id)
                    ->whereNotIn('id', $keepItemIds)
                    ->delete();
            }

            // Remove categories dropped entirely (cascades to their items
            // via the FK's cascadeOnDelete).
            MenuCategory::where('menu_id', $menu->id)
                ->whereNotIn('id', $keepCategoryIds)
                ->delete();

            return $menu->load('categories.items');
        });
    }
}
