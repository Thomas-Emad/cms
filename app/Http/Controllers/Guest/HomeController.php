<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(CurrentHotel $currentHotel): Response
    {
        $hotel = $currentHotel->get();

        // Phase 3 will replace this with PageRenderService pulling the
        // published homepage's section JSON. For now, just prove the
        // guest layout + theme pipeline works end to end.
        return Inertia::render('Guest/Home', [
            'hotel' => $hotel->only(['id', 'name']),
        ]);
    }
}
