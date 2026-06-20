<?php

namespace App\Http\Controllers\Larable;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * API Playground Controller
 *
 * Executes API requests from the Blade GUI sandbox.
 * Acts as a proxy to avoid CORS issues when testing from the backend GUI.
 */
class ApiPlaygroundController extends Controller
{
    /**
     * Execute an API request from the playground.
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:GET,POST,PUT,PATCH,DELETE'],
            'url' => ['required', 'string'],
            'headers' => ['nullable', 'array'],
            'body' => ['nullable', 'array'],
            'bearer_token' => ['nullable', 'string'],
        ]);

        $method = strtoupper($request->input('method'));
        $url = $request->input('url');
        $headers = $request->input('headers', []);
        $body = $request->input('body', []);
        $bearerToken = $request->input('bearer_token');

        // Build the full URL (resolve relative to current request host)
        if (str_starts_with($url, '/')) {
            $url = $request->getSchemeAndHttpHost().$url;
        }

        // Build headers
        $requestHeaders = array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $headers);

        if ($bearerToken) {
            $requestHeaders['Authorization'] = 'Bearer '.$bearerToken;
        }

        // Add idempotency key for mutation methods
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $requestHeaders['Idempotency-Key'] = 'playground-'.uniqid();
        }

        $startTime = microtime(true);

        try {
            $isLocal = str_starts_with($url, '/')
                || str_contains($url, 'localhost')
                || str_contains($url, '127.0.0.1')
                || str_contains($url, 'app')
                || str_contains($url, '.test')
                || parse_url($url, PHP_URL_HOST) === $request->getHost();

            if ($isLocal) {
                // Resolve path and query parameters
                $parsedUrl = parse_url($url);
                $uri = $parsedUrl['path'] ?? '/';
                if (isset($parsedUrl['query'])) {
                    $uri .= '?'.$parsedUrl['query'];
                }

                $content = null;
                $parameters = [];

                if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $content = json_encode($body);
                } else {
                    $parameters = $body ?? [];
                }

                // Create internal request
                $internalRequest = Request::create($uri, $method, $parameters, [], [], [], $content);

                // Set headers
                foreach ($requestHeaders as $key => $value) {
                    $internalRequest->headers->set($key, $value);
                }

                $originalRequest = request();
                $kernel = app(Kernel::class);
                $responseObj = $kernel->handle($internalRequest);
                app()->instance('request', $originalRequest);

                $duration = round((microtime(true) - $startTime) * 1000, 2);
                $responseBody = $responseObj->getContent();
                $decodedBody = json_decode($responseBody, true);

                return response()->json([
                    'status' => $responseObj->getStatusCode(),
                    'status_text' => $this->getStatusText($responseObj->getStatusCode()),
                    'headers' => $responseObj->headers->all(),
                    'body' => $decodedBody !== null ? $decodedBody : $responseBody,
                    'duration_ms' => $duration,
                    'size_bytes' => strlen($responseBody),
                ]);
            }

            // Fallback for actual external requests
            $httpClient = Http::withHeaders($requestHeaders)->timeout(30);

            $response = match ($method) {
                'GET' => $httpClient->get($url),
                'POST' => $httpClient->post($url, $body),
                'PUT' => $httpClient->put($url, $body),
                'PATCH' => $httpClient->patch($url, $body),
                'DELETE' => $httpClient->delete($url, $body),
            };

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'status' => $response->status(),
                'status_text' => $this->getStatusText($response->status()),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
                'duration_ms' => $duration,
                'size_bytes' => strlen($response->body()),
            ]);
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'status' => 0,
                'status_text' => 'Connection Error',
                'headers' => [],
                'body' => ['error' => $e->getMessage()],
                'duration_ms' => $duration,
                'size_bytes' => 0,
            ], 200); // Return 200 so the playground can display the error
        }
    }

    /**
     * Get human-readable status text.
     */
    protected function getStatusText(int $status): string
    {
        $texts = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
        ];

        return $texts[$status] ?? 'Unknown';
    }
}
