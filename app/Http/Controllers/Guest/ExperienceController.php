<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function index(Request $request): Response
    {
        $branchId = app(CurrentHotel::class)->branch()?->id;

        return Inertia::render('Guest/Experiences/Index', [
            'category' => $request->string('category')->value() ?: null,
            'experiences' => Experience::query()
                ->published()
                ->forBranch($branchId)
                ->category($request->string('category')->value() ?: null)
                ->with('cover', 'translations')
                ->ordered()
                ->get()
                ->map(fn (Experience $e) => [
                    'id' => $e->id,
                    'slug' => $e->slug,
                    'category' => $e->category,
                    'price' => $e->price,
                    'cover_image_url' => $e->cover_image_url,
                    // Translatable:
                    'title' => $e->title,
                    'duration' => $e->duration,
                ]),
        ]);
    }

    public function show(Experience $experience): Response
    {
        abort_unless($experience->status === 'published', 404);

        $experience->load('gallery', 'translations');

        return Inertia::render('Guest/Experiences/Show', [
            'experience' => [
                'id' => $experience->id,
                'slug' => $experience->slug,
                'category' => $experience->category,
                'price' => $experience->price,
                'booking_url' => $experience->booking_url,
                'status' => $experience->status,
                'featured' => $experience->featured,
                'cover_image_url' => $experience->cover_image_url,
                'gallery_urls' => $experience->gallery->pluck('url'),
                // Translatable:
                'title' => $experience->title,
                'description' => $experience->description,
                'short_description' => $experience->short_description,
                'duration' => $experience->duration,
            ],
        ]);
    }
}
