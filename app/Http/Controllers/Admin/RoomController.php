<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Models\Media;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Room::class);

        $branchId = $request->integer('branch_id') ?: null;

        return Inertia::render('Admin/Rooms/Index', [
            'selected_branch_id' => $branchId,
            'rooms' => Room::query()
                ->when($branchId, fn ($q) => $q->where('hotel_branch_id', $branchId))
                ->ordered()
                ->with(['cover', 'branch:id,name,city'])
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Room $r) => [
                    ...$r->only(['id', 'name', 'slug', 'status', 'featured', 'sort_order', 'hotel_branch_id']),
                    'branch' => $r->branch ? ['id' => $r->branch->id, 'name' => $r->branch->name, 'city' => $r->branch->city] : null,
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
