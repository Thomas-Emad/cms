<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class EventPolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return $this->belongsToUsersHotel($user, $event);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Event $event): bool
    {
        return $this->belongsToUsersHotel($user, $event)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->belongsToUsersHotel($user, $event)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
