<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebAccessMiddleware
{
    /**
     * Block worker/client roles from accessing the web interface.
     * These roles are for the mobile app only.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Roles blocked from web access
        $blockedRoles = ['worker', 'client'];

        $hasWebAccess = $user->roles()
            ->whereNotIn('name', $blockedRoles)
            ->exists();

        if (!$hasWebAccess) {
            abort(403, 'Esta conta é para a aplicação móvel e não tem acesso à interface web.');
        }

        return $next($request);
    }
}
