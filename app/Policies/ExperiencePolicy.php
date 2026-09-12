<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class ExperiencePolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Experience $experience): bool
    {
        return $this->belongsToUsersHotel($user, $experience);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Experience $experience): bool
    {
        return $this->belongsToUsersHotel($user, $experience)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $this->belongsToUsersHotel($user, $experience)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
