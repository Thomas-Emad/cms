<?php

namespace App\Http\Middleware;

use App\Models\Hotel;
use App\Services\Tenancy\CurrentHotel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active Hotel for this request and stores it in the
 * CurrentHotel singleton for the rest of the request lifecycle.
 *
 * Phase 1: single hotel, resolved by "first active hotel" (see CurrentHotel
 * fallback) — this middleware mostly exists so the seam is already wired
 * into the HTTP kernel.
 *
 * Phase 7: uncomment the domain-based resolution below and remove the
 * fallback in CurrentHotel::get().
 */
class ResolveCurrentHotel
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var CurrentHotel $currentHotel */
        $currentHotel = app(CurrentHotel::class);

        // --- Phase 7 domain resolution (disabled for now) ---
        // $host = $request->getHost();
        // $hotel = Hotel::where('domain', $host)->first()
        //     ?? Hotel::where('slug', explode('.', $host)[0])->first();
        // if ($hotel) {
        //     $currentHotel->set($hotel);
        // }

        // Phase 1: rely on CurrentHotel's single-tenant fallback,
        // just force resolution now so failures surface early (404 vs
        // a confusing later error).
        $currentHotel->get();

        return $next($request);
    }
}
