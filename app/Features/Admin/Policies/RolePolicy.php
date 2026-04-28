<?php

namespace App\Features\Admin\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\Role;
use App\Shared\Models\User;

class RolePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->hasPermission($user, 'view', 'roles');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->hasPermission($user, 'update', 'roles');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->hasPermission($user, 'delete', 'roles');
    }

    public function restore(User $user, Role $role): bool
    {
        return $this->hasPermission($user, 'restore', 'roles');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $this->hasPermission($user, 'force_delete', 'roles');
    }
}
