<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CurrentHotel $currentHotel): Response|RedirectResponse
    {
        if ($request->user()?->isSuperAdmin()) {
            return redirect()->route('admin.platform.dashboard');
        }

        return Inertia::render('Admin/Dashboard', [
            'hotel' => $currentHotel->get()->only(['id', 'name', 'slug', 'status']),
        ]);
    }
}
