<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\User;

class HotelPolicy
{
    public function view(User $user, Hotel $hotel): bool
    {
        return $user->isSuperAdmin() || $user->hotel_id === $hotel->id;
    }

    public function update(User $user, Hotel $hotel): bool
    {
        return $user->isSuperAdmin()
            || ($user->isHotelAdmin() && $user->hotel_id === $hotel->id);
    }

    // create/delete intentionally omitted in Phase 1 — hotel provisioning
    // is a Phase 7 (SaaS onboarding) concern; only super_admin will do it,
    // likely via seeder/console command rather than a UI action for now.
}
