<?php

namespace App\Policies;

use App\Models\HotelBranch;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class HotelBranchPolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HotelBranch $branch): bool
    {
        return $this->belongsToUsersHotel($user, $branch);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, HotelBranch $branch): bool
    {
        return $this->belongsToUsersHotel($user, $branch)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, HotelBranch $branch): bool
    {
        return $this->belongsToUsersHotel($user, $branch)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
