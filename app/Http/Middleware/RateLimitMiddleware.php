<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->user()?->id ?? $request->ip();
        $limit = 60;
        $decay = 60;

        if (! RateLimiter::attempt("global:{$key}", $limit, fn () => true, $decay)) {
            $retryAfter = RateLimiter::availableIn("global:{$key}");

            return response()->json([
                'message' => 'Too many requests',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }

        return $next($request);
    }
}
