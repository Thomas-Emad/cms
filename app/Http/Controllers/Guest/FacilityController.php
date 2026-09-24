<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Media;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->listing($request->string('category')->value() ?: null, __('facilities.title'));
    }

    /**
     * Meeting rooms are ordinary Facilities with category "meeting" - same
     * data, same admin screen, same detail page - just a dedicated entry
     * point for the guest screen's "Meeting Room" button.
     */
    public function meetingRooms(): Response
    {
        return $this->listing('meeting', __('facilities.meeting_rooms_title'));
    }

    private function listing(?string $category, string $title): Response
    {
        return Inertia::render('Guest/Facilities/Index', [
            'title' => $title,
            'category' => $category,
            'facilities' => Facility::query()
                ->published()
                ->category($category)
                ->with('cover')
                ->ordered()
                ->get()
                ->map(fn(Facility $f) => [
                    ...$f->only(['id', 'name', 'slug', 'short_description', 'category']),
                    'cover_image_url' => $f->cover_image_url,
                ]),
        ]);
    }

    public function show(Facility $facility): Response
    {
        abort_unless($facility->status === 'published', 404);

        $facility->load('cover', 'gallery');

        return Inertia::render('Guest/Facilities/Show', [
            'facility' => [
                ...$facility->toArray(),
                'gallery_urls' => $facility->gallery->pluck('url'),
            ],
            'slides' => collect([$facility->cover])->filter()->concat($facility->gallery)
                ->map(fn(Media $m) => $m->toPayload())->values(),
        ]);
    }
}
