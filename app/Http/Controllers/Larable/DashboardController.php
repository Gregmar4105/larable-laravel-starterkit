<?php

namespace App\Http\Controllers\Larable;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Larable Dashboard Controller
 *
 * Introspects all API routes and renders the backend GUI dashboard.
 * Groups endpoints by prefix and identifies HTTP methods.
 */
class DashboardController extends Controller
{
    /**
     * Render the main Larable dashboard.
     */
    public function index()
    {
        $endpoints = $this->getApiEndpoints();

        return view('larable.dashboard', [
            'endpoints' => $endpoints,
        ]);
    }

    /**
     * Return API endpoints as JSON (for AJAX sidebar updates).
     */
    public function endpoints(): JsonResponse
    {
        return response()->json($this->getApiEndpoints());
    }

    /**
     * Introspect all registered API routes.
     *
     * @return array<int, array{method: string, uri: string, name: string|null, middleware: array, group: string, description: string}>
     */
    protected function getApiEndpoints(): array
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'))
            ->map(function ($route) {
                $methods = collect($route->methods())
                    ->reject(fn ($m) => $m === 'HEAD')
                    ->values()
                    ->toArray();

                $uri = '/'.$route->uri();
                $group = $this->extractGroup($uri);
                $description = $this->generateDescription($methods[0] ?? 'GET', $uri);

                // Extract expected JSON keys from controller doc comments
                $bodyKeys = $this->extractBodyKeys($route);

                return [
                    'method' => $methods[0] ?? 'GET',
                    'methods' => $methods,
                    'uri' => $uri,
                    'name' => $route->getName(),
                    'middleware' => $route->middleware(),
                    'group' => $group,
                    'description' => $description,
                    'body_keys' => $bodyKeys,
                    'requires_auth' => in_array('auth:sanctum', $route->middleware()),
                ];
            })
            ->sortBy('uri')
            ->values()
            ->toArray();

        return $routes;
    }

    /**
     * Extract group name from URI for sidebar categorization.
     */
    protected function extractGroup(string $uri): string
    {
        // /api/v1/auth/login → auth
        $parts = explode('/', trim($uri, '/'));

        if (count($parts) >= 3) {
            // Skip 'api' and version prefix
            return $parts[2] ?? 'general';
        }

        return 'general';
    }

    /**
     * Generate a human-readable description for an endpoint.
     */
    protected function generateDescription(string $method, string $uri): string
    {
        $descriptions = [
            'GET /api/v1/health' => 'Check API health status and version information.',
            'GET /api/v1/auth/csrf-cookie' => 'Get CSRF cookie for SPA authentication. Call this before login/register.',
            'POST /api/v1/auth/login' => 'Authenticate with email and password. Returns a Bearer token. Supports remember_me for extended sessions.',
            'POST /api/v1/auth/register' => 'Create a new user account. Returns user data and Bearer token.',
            'POST /api/v1/auth/logout' => 'Revoke the current access token. Requires authentication.',
            'POST /api/v1/auth/forgot-password' => 'Send a password reset link to the provided email address.',
            'POST /api/v1/auth/reset-password' => 'Reset password using the token received via email.',
            'POST /api/v1/auth/two-factor/enable' => 'Enable two-factor authentication. Returns QR code and recovery codes.',
            'POST /api/v1/auth/two-factor/confirm' => 'Confirm 2FA setup with a valid TOTP code from your authenticator app.',
            'DELETE /api/v1/auth/two-factor/disable' => 'Disable two-factor authentication for this account.',
            'GET /api/v1/auth/two-factor/qr-code' => 'Get the QR code SVG for 2FA authenticator app setup.',
            'GET /api/v1/auth/two-factor/recovery-codes' => 'Get current recovery codes for 2FA backup access.',
            'POST /api/v1/auth/two-factor/recovery-codes' => 'Regenerate fresh recovery codes (invalidates previous ones).',
            'GET /api/v1/user' => 'Get the authenticated user\'s profile information.',
            'PUT /api/v1/user/profile' => 'Update the authenticated user\'s name and email.',
            'PUT /api/v1/user/password' => 'Change the authenticated user\'s password.',
        ];

        $key = $method.' '.$uri;

        return $descriptions[$key] ?? 'API endpoint: '.$method.' '.$uri;
    }

    /**
     * Extract expected request body keys from controller annotations.
     */
    protected function extractBodyKeys($route): array
    {
        $bodyKeyMap = [
            'POST /api/v1/auth/login' => [
                ['key' => 'email', 'type' => 'string', 'required' => true, 'example' => 'user@example.com'],
                ['key' => 'password', 'type' => 'string', 'required' => true, 'example' => ''],
                ['key' => 'remember_me', 'type' => 'boolean', 'required' => false, 'example' => false],
            ],
            'POST /api/v1/auth/register' => [
                ['key' => 'name', 'type' => 'string', 'required' => true, 'example' => 'John Doe'],
                ['key' => 'email', 'type' => 'string', 'required' => true, 'example' => 'john@example.com'],
                ['key' => 'password', 'type' => 'string', 'required' => true, 'example' => ''],
                ['key' => 'password_confirmation', 'type' => 'string', 'required' => true, 'example' => ''],
            ],
            'POST /api/v1/auth/forgot-password' => [
                ['key' => 'email', 'type' => 'string', 'required' => true, 'example' => 'user@example.com'],
            ],
            'POST /api/v1/auth/reset-password' => [
                ['key' => 'token', 'type' => 'string', 'required' => true, 'example' => ''],
                ['key' => 'email', 'type' => 'string', 'required' => true, 'example' => 'user@example.com'],
                ['key' => 'password', 'type' => 'string', 'required' => true, 'example' => ''],
                ['key' => 'password_confirmation', 'type' => 'string', 'required' => true, 'example' => ''],
            ],
            'POST /api/v1/auth/two-factor/confirm' => [
                ['key' => 'code', 'type' => 'string', 'required' => true, 'example' => '000000'],
            ],
            'PUT /api/v1/user/profile' => [
                ['key' => 'name', 'type' => 'string', 'required' => true, 'example' => 'Jane Doe'],
                ['key' => 'email', 'type' => 'string', 'required' => true, 'example' => 'jane@example.com'],
            ],
            'PUT /api/v1/user/password' => [
                ['key' => 'current_password', 'type' => 'string', 'required' => true, 'example' => ''],
                ['key' => 'password', 'type' => 'string', 'required' => true, 'example' => ''],
                ['key' => 'password_confirmation', 'type' => 'string', 'required' => true, 'example' => ''],
            ],
        ];

        $methods = collect($route->methods())->reject(fn ($m) => $m === 'HEAD')->values()->toArray();
        $key = ($methods[0] ?? 'GET').' /'.$route->uri();

        return $bodyKeyMap[$key] ?? [];
    }
}
