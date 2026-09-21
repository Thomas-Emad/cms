<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\HotelMap;
use App\Models\Page;
use App\Models\Restaurant;
use App\Models\Room;
use App\Services\Map\MapDataValidator;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin side of the hotel map. For now the map is managed as a validated JSON document
 * (paste / replace / reload the demo); a visual map editor can be built on top of the same
 * document later without touching the guest experience.
 */
class MapController extends Controller
{
    public const DEMO_FILE = 'database/data/demo-hotel-map.json';

    /** The visual map builder. */
    public function builder(): Response
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('view', $hotel);

        // Everything a place can link to, with whether guests can actually open it (published).
        $pub = fn ($m) => ($m->status ?? null) === 'published';
        $content = collect()
            ->merge(Facility::orderBy('name')->get(['name', 'slug', 'status'])->map(fn ($m) => ['type' => 'facility', 'slug' => $m->slug, 'name' => $m->name, 'published' => $pub($m)]))
            ->merge(Restaurant::orderBy('name')->get(['name', 'slug', 'status'])->map(fn ($m) => ['type' => 'restaurant', 'slug' => $m->slug, 'name' => $m->name, 'published' => $pub($m)]))
            ->merge(Room::orderBy('name')->get(['name', 'slug', 'status'])->map(fn ($m) => ['type' => 'room', 'slug' => $m->slug, 'name' => $m->name, 'published' => $pub($m)]))
            ->merge(Page::where('is_home', false)->orderBy('name')->get(['name', 'slug', 'status', 'published_version_id'])->map(fn ($m) => ['type' => 'page', 'slug' => $m->slug, 'name' => $m->name, 'published' => $m->status === 'published' && $m->published_version_id !== null]))
            ->values();

        return Inertia::render('Admin/Map/Builder', [
            'map' => HotelMap::query()->first()?->data,
            'content' => $content,
            'errors_list' => session()->pull('map_errors', []),
            'warnings' => session()->pull('map_warnings', []),
            'flash_ok' => session()->pull('map_saved'),
        ]);
    }

    /** Save from the builder (same validation as the JSON page). */
    public function save(Request $request, MapDataValidator $validator): RedirectResponse
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('update', $hotel);

        $request->validate(['data' => ['required', 'array']]);

        return $this->store($hotel->id, $request->input('data'), $validator, null, 'admin.map.builder');
    }

    public function edit(): Response
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('view', $hotel);

        $map = HotelMap::query()->first();
        $data = $map?->data;

        return Inertia::render('Admin/Map/Edit', [
            'has_map' => (bool) $map,
            'summary' => $data ? [
                'floors' => count($data['floors'] ?? []),
                'nodes' => count($data['nodes'] ?? []),
                'locations' => count($data['locations'] ?? []),
                'updated_at' => $map->updated_at?->toDateTimeString(),
            ] : null,
            // After a failed save, give the admin back exactly what they typed (not the stored map).
            'json' => session()->pull('map_json') ?? ($data ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''),
            'errors_list' => session()->pull('map_errors', []),
            'warnings' => session()->pull('map_warnings', []),
            'flash_ok' => session()->pull('map_saved'),
        ]);
    }

    public function update(Request $request, MapDataValidator $validator): RedirectResponse
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('update', $hotel);

        $request->validate(['json' => ['required', 'string', 'max:4000000']]);

        $raw = $request->input('json');
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fail(['That is not valid JSON: ' . json_last_error_msg() . '.'], $raw);
        }

        return $this->store($hotel->id, $data, $validator, $raw);
    }

    /** Replace the current map with the built-in demo hotel. */
    public function demo(MapDataValidator $validator): RedirectResponse
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('update', $hotel);

        $data = json_decode((string) file_get_contents(base_path(self::DEMO_FILE)), true);

        return $this->store($hotel->id, $data, $validator);
    }

    private function store(int $hotelId, mixed $data, MapDataValidator $validator, ?string $raw = null, string $route = 'admin.map.edit'): RedirectResponse
    {
        $result = $validator->validate($data);
        if ($result['errors']) {
            return $this->fail($result['errors'], $raw, $route);
        }

        HotelMap::withoutGlobalScopes()->updateOrCreate(['hotel_id' => $hotelId], ['data' => $data]);

        return redirect()->route($route)
            ->with('map_saved', 'Map saved.')
            ->with('map_warnings', $result['warnings']);
    }

    /** Show every problem at once (Inertia only surfaces the first message of an error bag). */
    private function fail(array $errors, ?string $raw, string $route = 'admin.map.edit'): RedirectResponse
    {
        return redirect()->route($route)
            ->with('map_errors', $errors)
            ->with('map_json', $raw);
    }
}
