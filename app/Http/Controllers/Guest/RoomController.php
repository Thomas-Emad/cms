<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Room;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $branchId = app(CurrentHotel::class)->branch()?->id;

        return Inertia::render('Guest/Rooms/Index', [
            'rooms' => Room::query()
                ->published()
                ->forBranch($branchId)
                ->with('cover', 'translations')
                ->ordered()
                ->get()
                ->map(fn (Room $r) => [
                    'id' => $r->id,
                    'slug' => $r->slug,
                    'size_sqm' => $r->size_sqm,
                    'max_guests' => $r->max_guests,
                    'cover_image_url' => $r->cover_image_url,
                    // Translatable:
                    'name' => $r->name,
                    'short_description' => $r->short_description,
                ]),
        ]);
    }

    public function show(Room $room): Response
    {
        abort_unless($room->status === 'published', 404);

        $room->load('cover', 'gallery', 'translations');

        return Inertia::render('Guest/Rooms/Show', [
            'room' => [
                'id' => $room->id,
                'slug' => $room->slug,
                'size_sqm' => $room->size_sqm,
                'max_guests' => $room->max_guests,
                'features' => $room->features,
                'cover_image_url' => $room->cover_image_url,
                // Played as a story on the room page: main photo first, then the gallery (photos and videos).
                'slides' => collect([$room->cover])->filter()->concat($room->gallery)
                    ->map(fn (Media $m) => $m->toPayload())->values(),
                // Translatable:
                'name' => $room->name,
                'short_description' => $room->short_description,
                'description' => $room->description,
                'bed_type' => $room->bed_type,
                'view' => $room->view,
            ],
        ]);
    }
}
