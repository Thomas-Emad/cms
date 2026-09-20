<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class RoomPolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Room $room): bool
    {
        return $this->belongsToUsersHotel($user, $room);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Room $room): bool
    {
        return $this->belongsToUsersHotel($user, $room)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->belongsToUsersHotel($user, $room)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
