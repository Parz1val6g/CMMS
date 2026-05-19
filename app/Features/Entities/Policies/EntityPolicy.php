<?php

namespace App\Features\Entities\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Policies\BasePolicy;
use App\Features\Entities\Models\Entity;
use App\Shared\Models\User;

class EntityPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::VIEW->value, PermissionResource::ENTITIES->value);
    }

    public function view(User $user, Entity $entity): bool
    {
        // Admin/manager can view all; entity user can view own
        if ($this->hasPermission($user, PermissionAction::VIEW->value, PermissionResource::ENTITIES->value)) {
            return true;
        }
        return $entity->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::CREATE->value, PermissionResource::ENTITIES->value);
    }

    public function update(User $user, Entity $entity): bool
    {
        if ($this->hasPermission($user, PermissionAction::UPDATE->value, PermissionResource::ENTITIES->value)) {
            return true;
        }
        return $entity->user_id === $user->id;
    }

    public function delete(User $user, Entity $entity): bool
    {
        return $this->isAdmin($user);
    }
}
