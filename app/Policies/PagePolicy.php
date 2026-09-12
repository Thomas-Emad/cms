<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class PagePolicy
{
    use AuthorizesTenantAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Page $page): bool
    {
        return $this->belongsToUsersHotel($user, $page);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function update(User $user, Page $page): bool
    {
        return $this->belongsToUsersHotel($user, $page)
            && in_array($user->role, ['super_admin', 'hotel_admin', 'hotel_staff'], true);
    }

    public function publish(User $user, Page $page): bool
    {
        // Publishing is gated more tightly than draft-editing: staff can
        // build a page, but going live is an admin decision.
        return $this->belongsToUsersHotel($user, $page)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->belongsToUsersHotel($user, $page)
            && in_array($user->role, ['super_admin', 'hotel_admin'], true);
    }
}
