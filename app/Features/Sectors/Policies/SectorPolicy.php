<?php

namespace App\Features\Sectors\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Sectors\Models\Sector;
use App\Shared\Models\User;

class SectorPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'sectors');
    }

    public function view(User $user, Sector $sector): bool
    {
        return $this->hasPermission($user, 'view', 'sectors');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'sectors');
    }

    public function update(User $user, Sector $sector): bool
    {
        return $this->hasPermission($user, 'update', 'sectors');
    }

    public function delete(User $user, Sector $sector): bool
    {
        return $this->hasPermission($user, 'delete', 'sectors');
    }

    public function restore(User $user, Sector $sector): bool
    {
        return $this->hasPermission($user, 'restore', 'sectors');
    }

    public function forceDelete(User $user, Sector $sector): bool
    {
        return $this->hasPermission($user, 'force_delete', 'sectors');
    }
}
