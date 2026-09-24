<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EntityPresentation;
use App\Models\Restaurant;
use App\Services\PageBuilder\PageRenderService;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Guest/Restaurants/Index', [
            'restaurants' => Restaurant::query()
                ->published()
                ->with('cover', 'translations')
                ->ordered()
                ->get()
                ->map(fn (Restaurant $r) => [
                    'id' => $r->id,
                    'slug' => $r->slug,
                    'cover_image_url' => $r->cover_image_url,
                    // Translatable:
                    'name' => $r->name,
                    'cuisine' => $r->cuisine,
                    'location' => $r->location,
                ]),
        ]);
    }

    /**
     * If this restaurant has a PUBLISHED custom presentation, render it
     * through the shared Builder pipeline. Otherwise, fall back to the
     * original hardcoded Show.vue exactly as before this feature existed
     * - every restaurant that has never been customized keeps working
     * unchanged, with zero migration required on existing data.
     */
    public function show(Restaurant $restaurant, PageRenderService $renderService, CurrentHotel $currentHotel): Response
    {
        abort_unless($restaurant->status === 'published', 404);

        $restaurant->loadMissing('translations');

        $presentation = EntityPresentation::query()
            ->where('presentable_type', Restaurant::class)
            ->where('presentable_id', $restaurant->id)
            ->where('status', 'published')
            ->first();

        if ($presentation && $presentation->publishedVersion) {
            $sections = $renderService->resolveSections(
                $presentation->publishedVersion->sections['sections'] ?? [],
                $currentHotel->get(),
                $restaurant
            );

            return Inertia::render('Guest/Restaurants/PresentationShow', [
                'restaurant' => $restaurant->only(['id', 'name', 'slug']),
                'sections' => $sections,
            ]);
        }

        $restaurant->load([
            'gallery',
            'translations',
            'activeMenu.categories.translations',
            'activeMenu.categories.items.translations',
        ]);

        $activeMenu = $restaurant->activeMenu ? [
            'id' => $restaurant->activeMenu->id,
            'name' => $restaurant->activeMenu->name,
            'categories' => $restaurant->activeMenu->categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'items' => $cat->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'dietary_info' => $item->dietary_info,
                    'is_available' => $item->is_available,
                ])->values(),
            ])->values(),
        ] : null;

        return Inertia::render('Guest/Restaurants/Show', [
            'restaurant' => [
                'id' => $restaurant->id,
                'slug' => $restaurant->slug,
                'phone' => $restaurant->phone,
                'floor' => $restaurant->floor,
                'opening_hours' => $restaurant->opening_hours,
                'reservation_url' => $restaurant->reservation_url,
                'status' => $restaurant->status,
                'featured' => $restaurant->featured,
                'cover_image_url' => $restaurant->cover_image_url,
                'gallery_urls' => $restaurant->gallery->pluck('url'),
                'active_menu' => $activeMenu,
                // Translatable:
                'name' => $restaurant->name,
                'description' => $restaurant->description,
                'short_description' => $restaurant->short_description,
                'cuisine' => $restaurant->cuisine,
                'dress_code' => $restaurant->dress_code,
                'location' => $restaurant->location,
            ],
        ]);
    }
}
