<?php

namespace App\Services\Tenancy;

use App\Models\Hotel;
use App\Models\HotelBranch;

/**
 * Resolves "which hotel are we currently operating as" for the duration
 * of a request. Bound as a singleton in TenancyServiceProvider.
 *
 * Resolution order:
 *   1. Explicitly set() — reserved for Phase 7 domain/subdomain resolution.
 *   2. The authenticated user's own hotel_id, if logged in and tenant-scoped.
 *      This is what keeps a hotel_admin/hotel_staff user's session locked to
 *      their own hotel once multiple hotels exist in the database.
 *   3. Single-tenant fallback (first active hotel) — only reachable when
 *      there's no authenticated user, i.e. the public guest site in today's
 *      one-hotel demo. This fallback is what Phase 7 removes once every
 *      guest request resolves a hotel from its domain instead.
 *
 * NOTE for Octane/long-running workers: this singleton's state must be
 * reset between requests if the app is ever deployed on Octane, since a
 * hotel resolved for request A must not leak into request B. Not a concern
 * under traditional PHP-FPM (fresh container per request), which is the
 * assumed deployment model for now.
 */
class CurrentHotel
{
    protected ?Hotel $hotel = null;

    protected ?HotelBranch $branch = null;

    public function set(Hotel $hotel): void
    {
        $this->hotel = $hotel;
    }

    public function setBranch(?HotelBranch $branch): void
    {
        $this->branch = $branch;
    }

    public function branch(): ?HotelBranch
    {
        return $this->branch;
    }

    public function hasBranch(): bool
    {
        return $this->branch !== null;
    }

    public function get(): Hotel
    {
        if ($this->hotel !== null) {
            return $this->hotel;
        }

        $user = auth()->user();

        if ($user && $user->hotel_id !== null) {
            // Bypass the global scope explicitly: at this point we're
            // resolving the tenant itself, so scoping the Hotel query by
            // "current hotel" would be circular.
            $this->hotel = Hotel::query()->findOrFail($user->hotel_id);

            return $this->hotel;
        }

        // Single-tenant fallback for unauthenticated (guest) requests only.
        // Phase 7: replaced by domain-based resolution in
        // ResolveCurrentHotel; this branch is removed once every guest
        // request carries its own hotel context via subdomain/custom domain.
        $this->hotel = Hotel::query()->where('status', 'active')->firstOrFail();

        return $this->hotel;
    }

    public function id(): int
    {
        return $this->get()->id;
    }

    public function has(): bool
    {
        if ($this->hotel !== null) {
            return true;
        }

        $user = auth()->user();
        if ($user && $user->hotel_id !== null) {
            $this->get();

            return $this->hotel !== null;
        }

        return false;
    }

    public function clear(): void
    {
        $this->hotel = null;
        $this->branch = null;
    }
}
