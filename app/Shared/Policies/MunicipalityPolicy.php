<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\Municipality;
use App\Shared\Models\User;

class MunicipalityPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'municipalities');
    }

    public function view(User $user, Municipality $municipality): bool
    {
        return $this->hasPermission($user, 'view', 'municipalities');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'municipalities');
    }

    public function update(User $user, Municipality $municipality): bool
    {
        return $this->hasPermission($user, 'update', 'municipalities');
    }

    public function delete(User $user, Municipality $municipality): bool
    {
        return $this->hasPermission($user, 'delete', 'municipalities');
    }
}
