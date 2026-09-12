<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class FacilityPolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true; // any authenticated admin user can list their hotel's facilities
    }

    public function view(User $user, Facility $facility): bool
    {
        return $this->belongsToUsersHotel($user, $facility);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Facility $facility): bool
    {
        return $this->belongsToUsersHotel($user, $facility)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Facility $facility): bool
    {
        // Staff can create/edit but not delete - deletion reserved for admins.
        return $this->belongsToUsersHotel($user, $facility)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
