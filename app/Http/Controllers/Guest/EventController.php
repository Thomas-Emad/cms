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
            'events' => Event::query()->published()->upcoming()->with('cover')->get()
                ->map(fn (Event $e) => [
                    ...$e->only(['id', 'title', 'slug', 'start_date', 'start_time', 'location']),
                    'cover_image_url' => $e->cover_image_url,
                ]),
        ]);
    }

    public function show(Event $event): Response
    {
        abort_unless($event->status === 'published', 404);

        $event->load('gallery');

        return Inertia::render('Guest/Events/Show', [
            'event' => [
                ...$event->toArray(),
                'gallery_urls' => $event->gallery->pluck('url'),
            ],
        ]);
    }
}
