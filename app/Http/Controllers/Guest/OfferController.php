<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(): Response
    {
        $branchId = app(CurrentHotel::class)->branch()?->id;

        return Inertia::render('Guest/Offers/Index', [
            'offers' => Offer::query()->active()->forBranch($branchId)->with('cover', 'translations')->latest()->get()
                ->map(fn (Offer $o) => [
                    'id' => $o->id,
                    'slug' => $o->slug,
                    'price' => $o->price,
                    'valid_until' => $o->valid_until,
                    'cover_image_url' => $o->cover_image_url,
                    // Translatable:
                    'title' => $o->title,
                    'discount' => $o->discount,
                ]),
        ]);
    }

    public function show(Offer $offer): Response
    {
        abort_unless($offer->status === 'published', 404);

        $offer->load('gallery', 'translations');

        return Inertia::render('Guest/Offers/Show', [
            'offer' => [
                'id' => $offer->id,
                'slug' => $offer->slug,
                'price' => $offer->price,
                'valid_from' => $offer->valid_from,
                'valid_until' => $offer->valid_until,
                'booking_url' => $offer->booking_url,
                'status' => $offer->status,
                'featured' => $offer->featured,
                'cover_image_url' => $offer->cover_image_url,
                'gallery_urls' => $offer->gallery->pluck('url'),
                // Translatable:
                'title' => $offer->title,
                'description' => $offer->description,
                'short_description' => $offer->short_description,
                'discount' => $offer->discount,
            ],
        ]);
    }
}
