<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Media;
use App\Services\Tenancy\CurrentHotel;
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
        $branchId = app(CurrentHotel::class)->branch()?->id;

        return Inertia::render('Guest/Facilities/Index', [
            'title' => $title,
            'category' => $category,
            'facilities' => Facility::query()
                ->published()
                ->forBranch($branchId)
                ->category($category)
                ->with('cover', 'translations')
                ->ordered()
                ->get()
                ->map(fn (Facility $f) => [
                    'id' => $f->id,
                    'slug' => $f->slug,
                    'category' => $f->category,
                    'cover_image_url' => $f->cover_image_url,
                    // Translatable:
                    'name' => $f->name,
                    'short_description' => $f->short_description,
                ]),
        ]);
    }

    public function show(Facility $facility): Response
    {
        abort_unless($facility->status === 'published', 404);

        $facility->load('cover', 'gallery', 'translations');

        return Inertia::render('Guest/Facilities/Show', [
            'facility' => [
                'id' => $facility->id,
                'slug' => $facility->slug,
                'status' => $facility->status,
                'category' => $facility->category,
                'phone' => $facility->phone,
                'email' => $facility->email,
                'opening_hours' => $facility->opening_hours,
                'amenities' => $facility->amenities,
                'featured' => $facility->featured,
                'sort_order' => $facility->sort_order,
                'cover_image_url' => $facility->cover_image_url,
                'gallery_urls' => $facility->gallery->pluck('url'),
                // Translatable — resolved through getAttribute() so the current locale is applied:
                'name' => $facility->name,
                'short_description' => $facility->short_description,
                'description' => $facility->description,
                'building' => $facility->building,
                'floor' => $facility->floor,
                'wing' => $facility->wing,
            ],
            'slides' => collect([$facility->cover])->filter()->concat($facility->gallery)
                ->map(fn (Media $m) => $m->toPayload())->values(),
        ]);
    }
}
