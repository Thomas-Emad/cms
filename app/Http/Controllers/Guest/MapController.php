<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\HotelMap;
use App\Services\Map\MapContentLinker;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function __invoke(Request $request, MapContentLinker $linker): Response
    {
        // HotelMap is tenant-scoped, so this is always the current hotel's map (or null).
        $map = HotelMap::query()->first();

        return Inertia::render('Guest/Map', [
            'map' => $map ? $linker->link($map->data) : null,
            // /map?place=spa opens the map with that place selected (handy for QR codes and "show on map" links).
            'place' => is_string($request->query('place')) ? $request->query('place') : null,
        ]);
    }
}
