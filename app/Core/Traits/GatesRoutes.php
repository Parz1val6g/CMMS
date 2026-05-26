<?php

namespace App\Core\Traits;

use App\Core\Services\PermissionManager;

trait GatesRoutes
{
    /**
     * Filter a routes array so that store/update/destroy are null
     * when the authenticated user lacks the corresponding permission.
     * All other keys (index, show, custom action routes) pass through unchanged.
     */
    protected function gatedRoutes(array $routes, string $resource): array
    {
        $user = request()->user();
        if (!$user) return $routes;

        /** @var PermissionManager $pm */
        $pm = app(PermissionManager::class);

        foreach (['store' => 'create', 'update' => 'update', 'destroy' => 'delete'] as $routeKey => $permAction) {
            if (array_key_exists($routeKey, $routes) && !$pm->hasPermission($user, $permAction, $resource)) {
                $routes[$routeKey] = null;
            }
        }

        return $routes;
    }
}
