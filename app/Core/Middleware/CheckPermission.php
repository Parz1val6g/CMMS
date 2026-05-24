<?php

namespace App\Core\Middleware;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Services\PermissionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function __construct(private PermissionManager $permissionManager) {}

    public function handle(Request $request, Closure $next, string $resource, string $action): Response
    {
        $resourceEnum = PermissionResource::tryFrom($resource);
        $actionEnum   = PermissionAction::tryFrom($action);

        if ($resourceEnum === null || $actionEnum === null) {
            abort(500);
        }

        $user = $request->user();

        if (!$this->permissionManager->hasPermission($user, $actionEnum->value, $resourceEnum->value)) {
            abort(403);
        }

        return $next($request);
    }
}
