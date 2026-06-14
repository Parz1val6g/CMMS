<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\Unit;
use App\Shared\Models\User;

class UnitPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'units');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->hasPermission($user, 'view', 'units');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'units');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->hasPermission($user, 'update', 'units');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->hasPermission($user, 'delete', 'units');
    }
}
