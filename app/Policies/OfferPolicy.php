<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class OfferPolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Offer $offer): bool
    {
        return $this->belongsToUsersHotel($user, $offer);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Offer $offer): bool
    {
        return $this->belongsToUsersHotel($user, $offer)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $this->belongsToUsersHotel($user, $offer)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
