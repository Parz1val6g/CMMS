<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level feature gating.
 *
 * Usage in routes:
 *   Route::middleware(['auth', 'feature:analytics'])->group(...);
 *
 * Returns 404 when the feature flag is disabled,
 * so disabled features look like they never existed.
 */
class EnsureFeatureIsEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(config("features.{$feature}"), 404, "Feature [{$feature}] is not enabled.");

        return $next($request);
    }
}
