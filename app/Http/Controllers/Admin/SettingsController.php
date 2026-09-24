<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuestViewRequest;
use App\Models\HotelSettings;
use App\Services\Layout\GuestLayoutStore;
use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Settings page: controls overall hotel settings, guest view layout (classic vs tv),
     * and theme appearance.
     */
    public function index(CurrentHotel $currentHotel, GuestLayoutStore $layoutStore): Response
    {
        $hotel = $currentHotel->get();
        $this->authorize('view', $hotel);

        $layoutConfig = $layoutStore->forHotel($hotel->id);
        $template = $layoutConfig['template'] ?? ($hotel->settings?->guest_view ?? 'classic');

        return Inertia::render('Admin/Settings/Index', [
            'guestView' => $template === 'tv' ? 'tv' : 'classic',
        ]);
    }

    public function updateGuestView(UpdateGuestViewRequest $request, CurrentHotel $currentHotel, GuestLayoutStore $layoutStore): RedirectResponse
    {
        $hotel = $currentHotel->get();
        $guestView = $request->validated('guest_view');

        // 1. Update hotel_settings column
        HotelSettings::query()
            ->firstOrCreate(['hotel_id' => $hotel->id])
            ->update(['guest_view' => $guestView]);

        // 2. Synchronize layout metadata so GuestLayout.vue switches immediately
        $config = $layoutStore->forHotel($hotel->id);
        $config['template'] = $guestView;
        $layoutStore->save($hotel->id, $config);

        return back()->with('success', __('admin.messages.guest_view_updated'));
    }
}
