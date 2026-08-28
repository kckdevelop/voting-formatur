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
 * This middleware re-sends valid W3C Permissions-Policy headers allowing camera access.
 */
class AllowCameraPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Valid W3C Permissions-Policy header syntax (RFC 8941 structured field)
        // camera=(self "*") or camera=(*) allows camera on both same-origin and embedded scripts
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self "*"), microphone=(), geolocation=()'
        );
        $response->headers->set(
            'Feature-Policy',
            "camera '*'; microphone 'none'; geolocation 'none'"
        );

        return $response;
    }
}
