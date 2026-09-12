<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Guest/Services/Index', [
            'services' => Service::query()->published()->ordered()->get()
                ->map(fn (Service $s) => $s->only([
                    'id', 'name', 'slug', 'description', 'icon', 'price', 'request_enabled', 'contact',
                ])),
        ]);
    }
}
