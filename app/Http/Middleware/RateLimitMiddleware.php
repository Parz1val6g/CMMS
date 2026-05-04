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
        $route = $request->route()?->getName() ?? 'unknown';
        $limit = 60;
        $decay = 60;

        $rateKey = "{$route}:{$key}";

        if (! RateLimiter::attempt($rateKey, $limit, fn () => true, $decay)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            return response()->json([
                'message' => 'Too many requests',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }

        return $next($request);
    }
}
