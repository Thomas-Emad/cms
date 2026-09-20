<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function __invoke(): Response
    {
        $hotel = app(CurrentHotel::class)->get();

        return Inertia::render('Guest/Gallery/Index', [
            'images' => $hotel->gallery->map(fn (Media $m) => $m->toPayload())->values(),
        ]);
    }
}
