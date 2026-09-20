<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Room;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Upload / delete / reorder images for anything that uses HasMedia.
 *
 * The client never sends a class name - only a short key from TYPES - so it
 * can't point an upload at an arbitrary model. Every operation:
 *   1. resolves the owner through a tenant-scoped query (or, for the hotel
 *      itself, checks the id equals the current hotel),
 *   2. authorizes `update` on that owner via its existing policy.
 * hotel_id on the media row always comes from CurrentHotel, never the request.
 */
class MediaController extends Controller
{
    private const TYPES = [
        'facility' => Facility::class,
        'room' => Room::class,
        'hotel' => Hotel::class,
    ];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mediable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'mediable_id' => ['required', 'integer'],
            'collection' => ['required', Rule::in(['cover', 'gallery'])],
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $owner = $this->resolveOwner($data['mediable_type'], (int) $data['mediable_id']);
        $this->authorize('update', $owner);

        $hotelId = app(CurrentHotel::class)->id();
        $collection = $data['collection'];

        $created = DB::transaction(function () use ($owner, $hotelId, $collection, $data) {
            // A cover is a single image: uploading a new one replaces the old.
            if ($collection === 'cover') {
                $this->query($owner, 'cover')->get()->each(fn (Media $m) => $this->deleteMedia($m));
            }

            $next = (int) $this->query($owner, $collection)->max('sort_order') + 1;
            $items = [];

            foreach ($data['files'] as $file) {
                $items[] = Media::create([
                    'hotel_id' => $hotelId,
                    'disk' => 'public',
                    'path' => $file->store("hotels/{$hotelId}/media", 'public'),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'mediable_type' => get_class($owner),
                    'mediable_id' => $owner->getKey(),
                    'collection' => $collection,
                    'sort_order' => $next++,
                ])->toPayload();
            }

            return $items;
        });

        return response()->json(['items' => $created], 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorizeMedia($media);
        $this->deleteMedia($media);

        return response()->json(null, 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer'],
        ]);

        $items = Media::query()
            ->whereIn('id', $data['ids'])
            ->where('hotel_id', app(CurrentHotel::class)->id())
            ->get();

        abort_unless($items->count() === count(array_unique($data['ids'])), 404);

        // All ids must belong to the same owner + collection, or the request is malformed.
        $first = $items->first();
        abort_unless(
            $items->every(fn (Media $m) => $m->mediable_type === $first->mediable_type
                && $m->mediable_id === $first->mediable_id
                && $m->collection === $first->collection),
            422
        );
        $this->authorizeMedia($first);

        DB::transaction(function () use ($data, $items) {
            foreach (array_values($data['ids']) as $position => $id) {
                $items->firstWhere('id', $id)?->update(['sort_order' => $position]);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function resolveOwner(string $key, int $id): object
    {
        $class = self::TYPES[$key];

        if ($class === Hotel::class) {
            abort_unless($id === app(CurrentHotel::class)->id(), 404);

            return Hotel::query()->findOrFail($id);
        }

        // BelongsToHotel global scope makes this a same-tenant lookup.
        return $class::query()->findOrFail($id);
    }

    private function authorizeMedia(Media $media): void
    {
        abort_unless($media->hotel_id === app(CurrentHotel::class)->id(), 404);

        $owner = $media->mediable;
        abort_if($owner === null, 404);
        $this->authorize('update', $owner);
    }

    private function query(object $owner, string $collection)
    {
        return Media::query()
            ->where('mediable_type', get_class($owner))
            ->where('mediable_id', $owner->getKey())
            ->where('collection', $collection);
    }

    private function deleteMedia(Media $media): void
    {
        // Demo/seed media points at external URLs; only delete real files we stored.
        if (! str_starts_with($media->path, 'http')) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();
    }
}
