<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Shared rule for every content policy (Facility, Restaurant, Event, Offer,
 * Page, ...): super_admin can touch anything; hotel_admin/hotel_staff can
 * only touch records belonging to their own hotel.
 *
 * Individual policies still define per-action rules (e.g. hotel_staff may
 * view but not delete) — this trait only answers "is this even the right
 * tenant".
 */
trait AuthorizesTenantAccess
{
    protected function belongsToUsersHotel(User $user, $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hotel_id !== null && $user->hotel_id === $model->hotel_id;
    }
}
