<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Room;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Guest/Rooms/Index', [
            'rooms' => Room::query()
                ->published()
                ->with('cover')
                ->ordered()
                ->get()
                ->map(fn (Room $r) => [
                    ...$r->only(['id', 'name', 'slug', 'short_description', 'size_sqm', 'max_guests']),
                    'cover_image_url' => $r->cover_image_url,
                ]),
        ]);
    }

    public function show(Room $room): Response
    {
        abort_unless($room->status === 'published', 404);

        $room->load('cover', 'gallery');

        return Inertia::render('Guest/Rooms/Show', [
            'room' => [
                ...$room->only([
                    'id', 'name', 'slug', 'short_description', 'description',
                    'size_sqm', 'max_guests', 'bed_type', 'view', 'features',
                ]),
                'cover_image_url' => $room->cover_image_url,
                // Played as a story on the room page: main photo first, then the gallery (photos and videos).
                'slides' => collect([$room->cover])->filter()->concat($room->gallery)
                    ->map(fn (Media $m) => $m->toPayload())->values(),
            ],
        ]);
    }
}
