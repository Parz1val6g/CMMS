<?php

namespace App\Features\Equipments\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Equipments\Models\CountingType;
use App\Shared\Models\User;

class CountingTypePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'counting_types');
    }

    public function view(User $user, CountingType $countingType): bool
    {
        return $this->hasPermission($user, 'view', 'counting_types');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'counting_types');
    }

    public function update(User $user, CountingType $countingType): bool
    {
        return $this->hasPermission($user, 'update', 'counting_types');
    }

    public function delete(User $user, CountingType $countingType): bool
    {
        return $this->hasPermission($user, 'delete', 'counting_types');
    }

    public function restore(User $user, CountingType $countingType): bool
    {
        return $this->hasPermission($user, 'restore', 'counting_types');
    }

    public function forceDelete(User $user, CountingType $countingType): bool
    {
        return $this->hasPermission($user, 'force_delete', 'counting_types');
    }
}
