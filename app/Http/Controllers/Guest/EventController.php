<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Guest/Events/Index', [
            'events' => Event::query()->published()->upcoming()->with('cover', 'translations')->get()
                ->map(fn (Event $e) => [
                    'id' => $e->id,
                    'slug' => $e->slug,
                    'start_date' => $e->start_date,
                    'start_time' => $e->start_time,
                    'cover_image_url' => $e->cover_image_url,
                    // Translatable:
                    'title' => $e->title,
                    'location' => $e->location,
                ]),
        ]);
    }

    public function show(Event $event): Response
    {
        abort_unless($event->status === 'published', 404);

        $event->load('gallery', 'translations');

        return Inertia::render('Guest/Events/Show', [
            'event' => [
                'id' => $event->id,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'capacity' => $event->capacity,
                'booking_required' => $event->booking_required,
                'booking_url' => $event->booking_url,
                'status' => $event->status,
                'cover_image_url' => $event->cover_image_url,
                'gallery_urls' => $event->gallery->pluck('url'),
                // Translatable:
                'title' => $event->title,
                'description' => $event->description,
                'short_description' => $event->short_description,
                'location' => $event->location,
            ],
        ]);
    }
}
