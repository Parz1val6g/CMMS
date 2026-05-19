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
            // Map short-form DB value to Laravel locale code
            // 'pt'  → 'pt_PT'  (directory is resources/lang/pt_PT/)
            // 'en'  → 'en'     (directory is resources/lang/en/)
            $locale = $request->user()->locale === 'pt' ? 'pt_PT' : $request->user()->locale;
            App::setLocale($locale);
        }

        return $next($request);
    }
}
