<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Guest/Facilities/Index', [
            'category' => $request->string('category')->value() ?: null,
            'facilities' => Facility::query()
                ->published()
                ->category($request->string('category')->value() ?: null)
                ->with('cover')
                ->ordered()
                ->get()
                ->map(fn (Facility $f) => [
                    ...$f->only(['id', 'name', 'slug', 'short_description', 'category']),
                    'cover_image_url' => $f->cover_image_url,
                ]),
        ]);
    }

    public function show(Facility $facility): Response
    {
        abort_unless($facility->status === 'published', 404);

        $facility->load('gallery');

        return Inertia::render('Guest/Facilities/Show', [
            'facility' => [
                ...$facility->toArray(),
                'gallery_urls' => $facility->gallery->pluck('url'),
            ],
        ]);
    }
}
