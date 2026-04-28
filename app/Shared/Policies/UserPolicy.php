<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\User;

class UserPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'users');
    }

    public function view(User $user, User $target): bool
    {
        return $this->isOwner($user, $target) || $this->hasPermission($user, 'view', 'users');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'users');
    }

    public function update(User $user, User $target): bool
    {
        return $this->isOwner($user, $target) || $this->hasPermission($user, 'update', 'users');
    }

    public function delete(User $user, User $target): bool
    {
        return $this->hasPermission($user, 'delete', 'users');
    }

    public function restore(User $user, User $target): bool
    {
        return $this->hasPermission($user, 'restore', 'users');
    }

    public function forceDelete(User $user, User $target): bool
    {
        return $this->hasPermission($user, 'force_delete', 'users');
    }
}
