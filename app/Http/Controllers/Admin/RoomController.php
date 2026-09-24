<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Models\Media;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Room::class);

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => Room::query()
                ->ordered()
                ->with('cover')
                ->paginate(20)
                ->through(fn (Room $r) => [
                    ...$r->only(['id', 'name', 'slug', 'status', 'featured', 'sort_order']),
                    'cover_image_url' => $r->cover_image_url,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Room::class);

        return Inertia::render('Admin/Rooms/Edit', ['room' => null, 'cover' => [], 'gallery' => []]);
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $room = Room::create($request->validated());

        // Straight to the edit screen: photos can only be attached once the room exists.
        return redirect()->route('admin.rooms.edit', $room)
            ->with('success', __('admin.messages.room_created'));
    }

    public function edit(Room $room): Response
    {
        $this->authorize('update', $room);

        return Inertia::render('Admin/Rooms/Edit', [
            'room' => $room,
            'cover' => $room->cover ? [$room->cover->toPayload()] : [],
            'gallery' => $room->gallery->map(fn (Media $m) => $m->toPayload())->values(),
        ]);
    }

    public function update(StoreRoomRequest $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);
        $room->update($request->validated());

        return redirect()->route('admin.rooms.edit', $room)->with('success', __('admin.messages.room_saved'));
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);
        $room->delete();

        return redirect()->route('admin.rooms.index')->with('success', __('admin.messages.room_deleted'));
    }
}
