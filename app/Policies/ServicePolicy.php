<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class ServicePolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Service $service): bool
    {
        return $this->belongsToUsersHotel($user, $service);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Service $service): bool
    {
        return $this->belongsToUsersHotel($user, $service)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->belongsToUsersHotel($user, $service)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
