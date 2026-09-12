<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\CurrentHotel;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(CurrentHotel $currentHotel): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'hotel' => $currentHotel->get()->only(['id', 'name', 'slug', 'status']),
        ]);
    }
}
