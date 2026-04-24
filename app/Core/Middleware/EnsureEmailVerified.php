<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email not verified'], 403);
        }

        return $next($request);
    }
}
