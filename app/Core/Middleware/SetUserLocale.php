<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetUserLocale
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->locale) {
            App::setLocale($request->user()->locale);
        }

        return $next($request);
    }
}
