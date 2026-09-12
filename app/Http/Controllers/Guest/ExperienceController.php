<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Guest/Experiences/Index', [
            'category' => $request->string('category')->value() ?: null,
            'experiences' => Experience::query()
                ->published()
                ->category($request->string('category')->value() ?: null)
                ->with('cover')
                ->ordered()
                ->get()
                ->map(fn (Experience $e) => [
                    ...$e->only(['id', 'title', 'slug', 'category', 'duration', 'price']),
                    'cover_image_url' => $e->cover_image_url,
                ]),
        ]);
    }

    public function show(Experience $experience): Response
    {
        abort_unless($experience->status === 'published', 404);

        $experience->load('gallery');

        return Inertia::render('Guest/Experiences/Show', [
            'experience' => [
                ...$experience->toArray(),
                'gallery_urls' => $experience->gallery->pluck('url'),
            ],
        ]);
    }
}
