<?php

namespace App\Features\Equipments\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\User;

class EquipmentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'equipments');
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $this->hasPermission($user, 'view', 'equipments');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'equipments');
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $this->hasPermission($user, 'update', 'equipments');
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $this->hasPermission($user, 'delete', 'equipments');
    }

    public function restore(User $user, Equipment $equipment): bool
    {
        return $this->hasPermission($user, 'restore', 'equipments');
    }

    public function forceDelete(User $user, Equipment $equipment): bool
    {
        return $this->hasPermission($user, 'force_delete', 'equipments');
    }
}
