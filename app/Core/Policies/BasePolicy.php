<?php

namespace App\Core\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Enums\UserRole;
use App\Shared\Models\User;

class BasePolicy
{
    public function before(User $user): ?bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return null;
    }

    protected function isAdmin(User $user): bool
    {
        return $user->roles()->where('name', 'Admin')->exists();
    }

    protected function isOwner(User $user, ?User $owner): bool
    {
        if ($owner === null) {
            return false;
        }

        return $user->id === $owner->id;
    }

    protected function hasPermission(User $user, string $action, string $resource): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Check through the user's roles for the specific permission
        return $user->roles()->whereHas('permissions', function($query) use ($action, $resource) {
            $query->where('resource', $resource)->where('action', $action);
        })->exists();
    }

    protected function isManagerScoped(User $user, ?User $manager): bool
    {
        if ($manager === null) {
            return false;
        }

        // Assuming a role named 'Manager'
        return $user->id === $manager->id && $user->roles()->where('name', 'Manager')->exists();
    }
}
