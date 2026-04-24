<?php

namespace App\Core\Services;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionManager
{
    private const CACHE_TTL = 3600;

    public function hasPermission(User $user, string $action, string $resource): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $cacheKey = "permissions:{$user->id}:{$resource}:{$action}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $action, $resource) {
            return $user->rolePermissions()
                ->where('resource', $resource)
                ->where('action', $action)
                ->exists();
        });
    }

    public function invalidateUserPermissions(User $user): void
    {
        $actions = PermissionAction::cases();
        $resources = PermissionResource::cases();

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $cacheKey = "permissions:{$user->id}:{$resource->value}:{$action->value}";
                Cache::forget($cacheKey);
            }
        }
    }

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
}
