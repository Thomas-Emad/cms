<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\HotelBranch;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    /**
     * Dedicated guest view displaying hotel branches with tabs, photos, addresses, and details.
     */
    public function index(Request $request, CurrentHotel $currentHotel): Response
    {
        $hotel = $currentHotel->get();

        $branches = HotelBranch::query()
            ->where('hotel_id', $hotel->id)
            ->published()
            ->ordered()
            ->get()
            ->map(fn (HotelBranch $b) => [
                'id' => $b->id,
                'slug' => $b->slug,
                'name' => $b->name,
                'city' => $b->city,
                'address' => $b->address,
                'phone' => $b->phone,
                'email' => $b->email,
                'short_description' => $b->short_description,
                'description' => $b->description,
                'cover_image_url' => $b->cover_image_url,
                'gallery_urls' => $b->gallery_urls ?? [],
                'all_photos' => $b->all_photos,
                'features' => $b->features ?? [],
                'latitude' => $b->latitude,
                'longitude' => $b->longitude,
                'is_main' => $b->is_main,
            ]);

        $activeSlug = $request->query('branch')
            ?: ($currentHotel->hasBranch() ? $currentHotel->branch()?->slug : null)
            ?: ($branches->firstWhere('is_main', true)['slug'] ?? $branches->first()['slug'] ?? null);

        return Inertia::render('Guest/Branches/Index', [
            'branches' => $branches,
            'active_branch' => $activeSlug,
            'title' => __('branches.title'),
            'subtitle' => __('branches.subtitle'),
        ]);
    }
}
