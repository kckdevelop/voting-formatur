<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Override restrictive Permissions-Policy headers set by the web server (Nginx/Apache).
 * The hosting server sends: camera=(), microphone=(), geolocation=()
 * which blocks the QR code scanner camera access on mobile browsers.
 *
 * This middleware re-sends the header with camera allowed for same-origin pages.
 */
class AllowCameraPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Allow camera for same-origin (self), block microphone and geolocation
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self), microphone=(), geolocation=()'
        );

        return $response;
    }
}
