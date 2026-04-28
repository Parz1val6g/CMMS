<?php

namespace App\Features\Materials\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Materials\Models\Material;
use App\Shared\Models\User;

class MaterialPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'materials');
    }

    public function view(User $user, Material $material): bool
    {
        return $this->hasPermission($user, 'view', 'materials');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'materials');
    }

    public function update(User $user, Material $material): bool
    {
        return $this->hasPermission($user, 'update', 'materials');
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->hasPermission($user, 'delete', 'materials');
    }

    public function restore(User $user, Material $material): bool
    {
        return $this->hasPermission($user, 'restore', 'materials');
    }

    public function forceDelete(User $user, Material $material): bool
    {
        return $this->hasPermission($user, 'force_delete', 'materials');
    }
}
