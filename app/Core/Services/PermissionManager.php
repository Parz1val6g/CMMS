<?php

namespace App\Core\Services;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionManager
{
    private const CACHE_TTL = 3600;

    /**
     * Check a single permission, scoped to active_role when set.
     * Falls back to checking all user roles for token-based (API) requests.
     */
    public function hasPermission(User $user, string $action, string $resource): bool
    {
        $activeRole = request()->hasSession() ? request()->session()->get('active_role') : null;

        // Admin bypass only when active role is admin (or no role is selected)
        if ($user->isAdmin() && ($activeRole === null || $activeRole === 'admin')) {
            return true;
        }

        if ($activeRole) {
            return in_array("{$resource}:{$action}", $this->activeRolePermissions($user, $activeRole), true);
        }

        $cacheKey = "permissions:{$user->id}:all:{$resource}:{$action}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $action, $resource) {
            return $user->rolePermissions()
                ->where('resource', $resource)
                ->where('action', $action)
                ->exists();
        });
    }

    public function invalidateUserPermissions(User $user): void
    {
        $actions   = PermissionAction::cases();
        $resources = PermissionResource::cases();

        // Invalidate scoped-permission cache keys
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Cache::forget("permissions:{$user->id}:all:{$resource->value}:{$action->value}");
            }
        }

        // Invalidate aggregated permission arrays
        Cache::forget("user_permissions:{$user->id}");

        foreach ($user->roles as $role) {
            Cache::forget("user_permissions:{$user->id}:{$role->name}");
        }
    }

    /**
     * All permissions for a user across all their roles (used when no active_role is set).
     */
    public function userPermissions(User $user): array
    {
        $cacheKey = "user_permissions:{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $user->rolePermissions()
                ->get(['resource', 'action'])
                ->map(fn($p) => "{$p->resource}:{$p->action}")
                ->toArray();
        });
    }

    /**
     * All permissions for a specific role of the user (used when active_role is set).
     */
    public function activeRolePermissions(User $user, string $role): array
    {
        $cacheKey = "user_permissions:{$user->id}:{$role}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $role) {
            $roleId = $user->roles()->where('name', $role)->value('id');

            if (!$roleId) {
                return [];
            }

            return \App\Shared\Models\RolePermission::where('role_id', $roleId)
                ->get(['resource', 'action'])
                ->map(fn($p) => "{$p->resource}:{$p->action}")
                ->toArray();
        });
    }
}
