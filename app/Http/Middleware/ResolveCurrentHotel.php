<?php

namespace App\Http\Middleware;

use App\Models\Hotel;
use App\Models\HotelBranch;
use App\Services\Tenancy\CurrentHotel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentHotel
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var CurrentHotel $currentHotel */
        $currentHotel = app(CurrentHotel::class);

        $host = strtolower($request->getHost());

        // 1. Direct custom hotel domain match (e.g. smarttel.com or brand.com)
        $hotel = Hotel::where('domain', $host)->first();
        $branch = null;

        // 2. Direct custom branch domain match (e.g. cairo.smarttel.com, alexandria.smarttel.com)
        if (! $hotel) {
            $branch = HotelBranch::withoutGlobalScope('hotel')->where('domain', $host)->first();
            if ($branch) {
                $hotel = $branch->hotel;
            }
        }

        // 3. Subdomain / slug match (e.g., cairo.saas.com or hotel-slug.saas.com)
        if (! $hotel && str_contains($host, '.')) {
            $subdomain = explode('.', $host)[0];
            if ($subdomain !== 'www') {
                // Check branch slug first
                $branch = HotelBranch::withoutGlobalScope('hotel')->where('slug', $subdomain)->first();
                if ($branch) {
                    $hotel = $branch->hotel;
                } else {
                    $hotel = Hotel::where('slug', $subdomain)->first();
                }
            }
        }

        // 4. Fallback for testing / local development on localhost or 127.0.0.1
        if (! $hotel && (app()->environment('testing', 'local') && in_array($host, ['localhost', '127.0.0.1'], true))) {
            if ($currentHotel->has()) {
                $hotel = $currentHotel->get();
                $branch = $currentHotel->branch();
            } else {
                $user = $request->user();
                if ($user && $user->hotel_id !== null) {
                    $hotel = Hotel::find($user->hotel_id);
                }

                if (! $hotel) {
                    $hotel = Hotel::where('status', 'active')->first();
                }
            }
        }

        // 5. Unknown domain -> abort 404
        if (! $hotel) {
            abort(404, "Hotel not found for domain: {$host}");
        }

        $currentHotel->set($hotel);

        // If a specific branch was resolved, store it; otherwise pick the main branch if available (or clear if none)
        if ($branch) {
            $currentHotel->setBranch($branch);
        } else {
            $mainBranch = $hotel->branches()->where('is_main', true)->first();
            $currentHotel->setBranch($mainBranch);
        }

        return $this->verifyAccessAndStatus($request, $next, $hotel);
    }

    protected function verifyAccessAndStatus(Request $request, Closure $next, Hotel $hotel): Response
    {
        $user = $request->user();

        // If hotel is suspended: Super Admins can still view/manage, but guests and tenant users are blocked.
        if ($hotel->status === 'suspended') {
            if (! $user || ! $user->isSuperAdmin()) {
                abort(403, 'This hotel is currently suspended.');
            }
        }

        // Tenant boundary check: authenticated tenant users cannot access another hotel's context
        if ($user && ! $user->isSuperAdmin() && $user->hotel_id !== null) {
            if ($user->hotel_id !== $hotel->id) {
                abort(403, 'Unauthorized access to this hotel.');
            }
        }

        return $next($request);
    }
}
