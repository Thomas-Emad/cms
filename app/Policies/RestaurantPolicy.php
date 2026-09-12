<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class RestaurantPolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $this->belongsToUsersHotel($user, $restaurant);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $this->belongsToUsersHotel($user, $restaurant)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $this->belongsToUsersHotel($user, $restaurant)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }

    /**
     * Menu editing follows the restaurant's own update permission -
     * there's no separate "menu manager" role distinction in Phase 2.
     */
    public function manageMenu(User $user, Restaurant $restaurant): bool
    {
        return $this->update($user, $restaurant);
    }
}
