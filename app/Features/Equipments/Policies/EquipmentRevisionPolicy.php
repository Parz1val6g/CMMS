<?php

namespace App\Features\Equipments\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Equipments\Models\EquipmentRevision;
use App\Shared\Models\User;

class EquipmentRevisionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'equipment_revisions');
    }

    public function view(User $user, EquipmentRevision $equipmentRevision): bool
    {
        return $this->hasPermission($user, 'view', 'equipment_revisions');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'equipment_revisions');
    }

    public function update(User $user, EquipmentRevision $equipmentRevision): bool
    {
        return $this->hasPermission($user, 'update', 'equipment_revisions');
    }

    public function delete(User $user, EquipmentRevision $equipmentRevision): bool
    {
        return $this->hasPermission($user, 'delete', 'equipment_revisions');
    }

    public function restore(User $user, EquipmentRevision $equipmentRevision): bool
    {
        return $this->hasPermission($user, 'restore', 'equipment_revisions');
    }

    public function forceDelete(User $user, EquipmentRevision $equipmentRevision): bool
    {
        return $this->hasPermission($user, 'force_delete', 'equipment_revisions');
    }
}
