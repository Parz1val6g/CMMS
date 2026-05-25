<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebAccessMiddleware
{
    /**
     * Block client role from the web interface.
     * Workers are allowed — they need web access for mini-tasks and work logs (UC1).
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Entity users go to their own portal — allow through
        if ($user->isEntity()) {
            return $next($request);
        }

        // Only clients are blocked from the main web UI (UC1: client has no system access)
        $blockedRoles = ['client'];

        // When a specific role is active, check only against that role
        $activeRole = $request->session()->get('active_role');
        if ($activeRole) {
            if (in_array($activeRole, $blockedRoles)) {
                abort(403, 'Esta conta não tem acesso à interface web.');
            }
            return $next($request);
        }

        // Fallback: user has at least one non-blocked role
        $hasWebAccess = $user->roles()->whereNotIn('name', $blockedRoles)->exists();

        if (!$hasWebAccess) {
            abort(403, 'Esta conta não tem acesso à interface web.');
        }

        return $next($request);
    }
}
