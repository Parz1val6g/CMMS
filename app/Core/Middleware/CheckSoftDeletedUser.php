<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSoftDeletedUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($request->user()->trashed()) {
            auth()->logout();

            return response()->json(['message' => 'User account has been deleted'], 403);
        }

        return $next($request);
    }
}
