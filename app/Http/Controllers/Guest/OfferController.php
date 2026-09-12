<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Guest/Offers/Index', [
            'offers' => Offer::query()->active()->with('cover')->latest()->get()
                ->map(fn (Offer $o) => [
                    ...$o->only(['id', 'title', 'slug', 'price', 'discount', 'valid_until']),
                    'cover_image_url' => $o->cover_image_url,
                ]),
        ]);
    }

    public function show(Offer $offer): Response
    {
        abort_unless($offer->status === 'published', 404);

        $offer->load('gallery');

        return Inertia::render('Guest/Offers/Show', [
            'offer' => [
                ...$offer->toArray(),
                'gallery_urls' => $offer->gallery->pluck('url'),
            ],
        ]);
    }
}
