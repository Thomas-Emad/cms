<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
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

    public function show(Restaurant $restaurant): Response
    {
        abort_unless($restaurant->status === 'published', 404);

        $restaurant->load(['gallery', 'activeMenu.categories.items']);

        return Inertia::render('Guest/Restaurants/Show', [
            'restaurant' => [
                ...$restaurant->toArray(),
                'gallery_urls' => $restaurant->gallery->pluck('url'),
            ],
        ]);
    }
}
