<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idempotency Middleware
 *
 * Ensures that POST/PUT/PATCH requests with the same Idempotency-Key
 * header return the exact same response, preventing duplicate operations.
 *
 * Usage: Include `Idempotency-Key` header in your request.
 * The cached response is stored for 24 hours.
 */
class IdempotencyMiddleware
{
    /**
     * HTTP methods that support idempotency.
     */
    protected array $idempotentMethods = ['POST', 'PUT', 'PATCH'];

    /**
     * Cache TTL in seconds (24 hours).
     */
    protected int $cacheTtl = 86400;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to mutating methods
        if (! in_array($request->method(), $this->idempotentMethods)) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        // No key provided — proceed normally
        if (! $idempotencyKey) {
            return $next($request);
        }

        $cacheKey = $this->buildCacheKey($request, $idempotencyKey);

        // Check for existing cached response
        $cachedResponse = Cache::get($cacheKey);

        if ($cachedResponse !== null) {
            return response()
                ->json($cachedResponse['body'], $cachedResponse['status'])
                ->withHeaders(array_merge(
                    $cachedResponse['headers'],
                    ['X-Idempotency-Replay' => 'true']
                ));
        }

        // Execute the request
        $response = $next($request);

        // Cache the response
        Cache::put($cacheKey, [
            'status' => $response->getStatusCode(),
            'headers' => $this->extractCacheableHeaders($response),
            'body' => json_decode($response->getContent(), true),
        ], $this->cacheTtl);

        return $response;
    }

    /**
     * Build a unique cache key from the idempotency key and request context.
     */
    protected function buildCacheKey(Request $request, string $idempotencyKey): string
    {
        $userId = $request->user()?->id ?? 'guest';

        return "idempotency:{$userId}:{$idempotencyKey}";
    }

    /**
     * Extract headers worth caching (skip internal/framework headers).
     */
    protected function extractCacheableHeaders(Response $response): array
    {
        $headers = [];
        $include = ['Content-Type', 'X-Request-Id'];

        foreach ($include as $header) {
            if ($response->headers->has($header)) {
                $headers[$header] = $response->headers->get($header);
            }
        }

        return $headers;
    }
}
