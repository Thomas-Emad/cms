<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $branchId = app(CurrentHotel::class)->branch()?->id;

        return Inertia::render('Guest/Services/Index', [
            'services' => Service::query()->published()->forBranch($branchId)->with('translations')->ordered()->get()
                ->map(fn (Service $s) => [
                    'id' => $s->id,
                    'slug' => $s->slug,
                    'icon' => $s->icon,
                    'price' => $s->price,
                    'request_enabled' => $s->request_enabled,
                    'contact' => $s->contact,
                    // Translatable:
                    'name' => $s->name,
                    'description' => $s->description,
                ]),
        ]);
    }
}
