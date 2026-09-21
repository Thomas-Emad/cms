<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuestViewRequest;
use App\Models\HotelSettings;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Currently just the Guest View picker (classic vs tv). Named
     * generically ('Settings', not 'GuestView') since this is the natural
     * home for other site-wide hotel settings later - see the disabled
     * "Hotel Information" / "Theme" nav items this sits next to.
     */
    public function index(CurrentHotel $currentHotel): Response
    {
        $hotel = $currentHotel->get();
        $this->authorize('update', $hotel);

        return Inertia::render('Admin/Settings/Index', [
            // firstOrCreate: a hotel provisioned before HotelSettings
            // existed (or before this guest_view column existed) may not
            // have a settings row yet - default it to 'classic' rather
            // than 404ing or crashing this page.
            'guestView' => $hotel->settings?->guest_view
                ?? HotelSettings::query()->firstOrCreate(['hotel_id' => $hotel->id])->guest_view,
        ]);
    }

    public function updateGuestView(UpdateGuestViewRequest $request, CurrentHotel $currentHotel): RedirectResponse
    {
        $hotel = $currentHotel->get();

        HotelSettings::query()
            ->firstOrCreate(['hotel_id' => $hotel->id])
            ->update(['guest_view' => $request->validated('guest_view')]);

        return back()->with('success', 'Guest view updated.');
    }
}
