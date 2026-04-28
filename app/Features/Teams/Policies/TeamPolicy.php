<?php

namespace App\Features\Teams\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Teams\Models\Team;
use App\Shared\Models\User;

class TeamPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'teams');
    }

    public function view(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'view', 'teams');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'teams');
    }

    public function update(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'update', 'teams');
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'delete', 'teams');
    }

    public function restore(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'restore', 'teams');
    }

    public function forceDelete(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'force_delete', 'teams');
    }
}
