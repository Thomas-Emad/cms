<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $totalHotels = Hotel::count();
        $activeHotels = Hotel::where('status', 'active')->count();
        $suspendedHotels = Hotel::where('status', 'suspended')->count();
        $draftHotels = Hotel::where('status', 'draft')->count();

        $totalBranches = HotelBranch::withoutGlobalScope('hotel')->count();
        $totalUsers = User::whereNotNull('hotel_id')->count();

        $recentHotels = Hotel::with(['primaryAdmin', 'branches'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Hotel $h) => [
                'id' => $h->id,
                'name' => $h->name,
                'slug' => $h->slug,
                'domain' => $h->domain,
                'status' => $h->status,
                'branches_count' => $h->branches->count(),
                'primary_admin' => $h->primaryAdmin ? [
                    'name' => $h->primaryAdmin->name,
                    'email' => $h->primaryAdmin->email,
                ] : null,
                'created_at' => $h->created_at?->format('Y-m-d'),
            ]);

        $branchesWithDomains = HotelBranch::withoutGlobalScope('hotel')
            ->whereNotNull('domain')
            ->with('hotel')
            ->get()
            ->map(fn (HotelBranch $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'domain' => $b->domain,
                'hotel_id' => $b->hotel_id,
                'hotel_name' => $b->hotel?->name,
                'city' => $b->city,
                'is_main' => (bool) $b->is_main,
                'status' => $b->status,
            ]);

        return Inertia::render('Admin/Platform/Dashboard', [
            'metrics' => [
                'total_hotels' => $totalHotels,
                'active_hotels' => $activeHotels,
                'suspended_hotels' => $suspendedHotels,
                'draft_hotels' => $draftHotels,
                'total_branches' => $totalBranches,
                'total_users' => $totalUsers,
            ],
            'recent_hotels' => $recentHotels,
            'branches_with_domains' => $branchesWithDomains,
        ]);
    }
}
