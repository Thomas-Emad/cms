<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

/** Hotel-wide photo gallery: images attached to the Hotel itself (collection 'gallery'). */
class GalleryController extends Controller
{
    public function index(): Response
    {
        $hotel = app(CurrentHotel::class)->get();
        $this->authorize('view', $hotel);

        return Inertia::render('Admin/Gallery/Index', [
            'hotel_id' => $hotel->id,
            'items' => $hotel->gallery->map(fn (Media $m) => $m->toPayload())->values(),
        ]);
    }
}
