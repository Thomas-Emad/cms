<?php

namespace App\Http\Middleware;

use App\Services\Layout\GuestLayoutStore;
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
            // Which guest-screen template + menu to use. Not needed (so not queried) in the admin area.
            'guestLayout' => fn () => $request->is('admin*') || ! app(CurrentHotel::class)->has()
                ? null
                : app(GuestLayoutStore::class)->forHotel(app(CurrentHotel::class)->get()->id),
        ]);
    }
}
