<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotelMap;
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

    private function store(int $hotelId, mixed $data, MapDataValidator $validator, ?string $raw = null): RedirectResponse
    {
        $result = $validator->validate($data);
        if ($result['errors']) {
            return $this->fail($result['errors'], $raw);
        }

        HotelMap::withoutGlobalScopes()->updateOrCreate(['hotel_id' => $hotelId], ['data' => $data]);

        return redirect()->route('admin.map.edit')
            ->with('map_saved', 'Map saved.')
            ->with('map_warnings', $result['warnings']);
    }

    /** Show every problem at once (Inertia only surfaces the first message of an error bag). */
    private function fail(array $errors, ?string $raw): RedirectResponse
    {
        return redirect()->route('admin.map.edit')
            ->with('map_errors', $errors)
            ->with('map_json', $raw);
    }
}
