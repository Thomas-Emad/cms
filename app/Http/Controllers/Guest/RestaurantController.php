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
                ->with('cover')
                ->ordered()
                ->get()
                ->map(fn (Restaurant $r) => [
                    ...$r->only(['id', 'name', 'slug', 'cuisine', 'location']),
                    'cover_image_url' => $r->cover_image_url,
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

        $restaurant->load(['gallery', 'activeMenu.categories.items']);

        return Inertia::render('Guest/Restaurants/Show', [
            'restaurant' => [
                ...$restaurant->toArray(),
                'gallery_urls' => $restaurant->gallery->pluck('url'),
            ],
        ]);
    }
}
