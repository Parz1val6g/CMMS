<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Don't expose server software
        $response->header('Server', 'Web Server');

        // Prevent clickjacking
        $response->header('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->header('X-XSS-Protection', '1; mode=block');

        // Content Security Policy — disable in development (Vite dev server uses dynamic ports/IPs), strict in production
        if (!app()->isProduction()) {
            // Development: disable CSP to allow Vite HMR, dev tools, and data URIs
            $response->header('Content-Security-Policy', "default-src *; script-src * 'unsafe-inline' 'unsafe-eval'; style-src * 'unsafe-inline'; img-src * data:; connect-src * ws: wss:;");
        } else {
            // Production: strict CSP
            $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; font-src 'self' https://fonts.bunny.net; img-src 'self' data: https:; connect-src 'self'");
        }

        // Prevent referrer leakage
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}