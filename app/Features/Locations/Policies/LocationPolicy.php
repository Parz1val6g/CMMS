<?php

namespace App\Features\Locations\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Locations\Models\Location;
use App\Shared\Models\User;

class LocationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'locations');
    }

    public function view(User $user, Location $location): bool
    {
        return $this->hasPermission($user, 'view', 'locations');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'locations');
    }

    public function update(User $user, Location $location): bool
    {
        return $this->hasPermission($user, 'update', 'locations');
    }

    public function delete(User $user, Location $location): bool
    {
        return $this->hasPermission($user, 'delete', 'locations');
    }

    public function restore(User $user, Location $location): bool
    {
        return $this->hasPermission($user, 'restore', 'locations');
    }

    public function forceDelete(User $user, Location $location): bool
    {
        return $this->hasPermission($user, 'force_delete', 'locations');
    }
}
