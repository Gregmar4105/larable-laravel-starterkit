<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Version Middleware
 *
 * Reads the API version from the URL prefix and makes it available
 * on the request object via $request->attributes.
 *
 * Usage: Access version with $request->attributes->get('api_version')
 */
class ApiVersionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $version = 'v1'): Response
    {
        $request->attributes->set('api_version', $version);

        $response = $next($request);

        // Add version header to response
        $response->headers->set('X-API-Version', $version);

        return $response;
    }
}
