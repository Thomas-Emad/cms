<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\CurrentHotel;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()
                    ? $request->user()->only(['id', 'name', 'email', 'role', 'hotel_id'])
                    : null,
            ],
            'hotel' => fn () => app(CurrentHotel::class)->has()
                ? app(CurrentHotel::class)->get()->only(['id', 'name', 'slug', 'status'])
                : null,
            // Site-wide guest shell choice ('classic' | 'tv') - read by
            // GuestShell.vue to decide which layout component wraps every
            // guest page. Defaults to 'classic' if settings row is
            // missing so a hotel with no HotelSettings row yet still
            // renders exactly as before this feature existed.
            'guestView' => fn () => app(CurrentHotel::class)->has()
                ? (app(CurrentHotel::class)->get()->settings?->guest_view ?? 'classic')
                : 'classic',
        ]);
    }
}
