<?php

namespace App\Features\Equipments\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Equipments\Models\EquipmentType;
use App\Shared\Models\User;

class EquipmentTypePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'equipment_types');
    }

    public function view(User $user, EquipmentType $equipmentType): bool
    {
        return $this->hasPermission($user, 'view', 'equipment_types');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'equipment_types');
    }

    public function update(User $user, EquipmentType $equipmentType): bool
    {
        return $this->hasPermission($user, 'update', 'equipment_types');
    }

    public function delete(User $user, EquipmentType $equipmentType): bool
    {
        return $this->hasPermission($user, 'delete', 'equipment_types');
    }

    public function restore(User $user, EquipmentType $equipmentType): bool
    {
        return $this->hasPermission($user, 'restore', 'equipment_types');
    }

    public function forceDelete(User $user, EquipmentType $equipmentType): bool
    {
        return $this->hasPermission($user, 'force_delete', 'equipment_types');
    }
}
