<?php

namespace App\Core\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Enums\UserRole;
use App\Shared\Models\User;

class BasePolicy
{
    /**
     * Per-request permission cache — avoids N+1 on listing endpoints.
     * @var array<string, bool>
     */
    private array $permCache = [];

    public function before(User $user, string $ability): ?bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return null;
    }

    protected function isAdmin(User $user): bool
    {
        $key = 'admin:' . $user->id;

        if (!isset($this->permCache[$key])) {
            $this->permCache[$key] = $user->roles()
                ->where('name', 'admin')
                ->exists();
        }

        return $this->permCache[$key];
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

        $key = $user->id . ':' . $action . '@' . $resource;

        if (!isset($this->permCache[$key])) {
            $this->permCache[$key] = $user->roles()
                ->whereHas('permissions', function ($query) use ($action, $resource) {
                    $query->where('resource', $resource)
                          ->where('action', $action);
                })->exists();
        }

        return $this->permCache[$key];
    }

    protected function isManagerScoped(User $user, ?User $manager): bool
    {
        if ($manager === null) {
            return false;
        }

        return $user->id === $manager->id && $this->hasRole($user, 'manager');
    }

    protected function hasRole(User $user, string $role): bool
    {
        $key = 'role:' . $user->id . ':' . $role;

        if (!isset($this->permCache[$key])) {
            $this->permCache[$key] = $user->roles()
                ->where('name', $role)
                ->exists();
        }

        return $this->permCache[$key];
    }
}
