<?php

use App\Http\Middleware\ApiVersionMiddleware;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\LarableAuthMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (required for reverse proxies, load balancers, Docker)
        $middleware->trustProxies(at: '*');

        // Register middleware aliases
        $middleware->alias([
            'idempotency' => IdempotencyMiddleware::class,
            'api.version' => ApiVersionMiddleware::class,
            'larable.auth' => LarableAuthMiddleware::class,
        ]);

        // Configure Sanctum stateful middleware for SPA auth
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always render JSON for API routes in production
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        if (config('app.debug')) {
            $exceptions->render(function (Throwable $e, Request $request) {
                $exemptions = [
                    ValidationException::class,
                    AuthenticationException::class,
                    AuthorizationException::class,
                    HttpExceptionInterface::class,
                ];

                foreach ($exemptions as $exemption) {
                    if ($e instanceof $exemption) {
                        return null;
                    }
                }

                if ($request->is('api/*') || $request->expectsJson()) {
                    // Force the default handler to render the standard HTML debug page (Ignition)
                    // so the developer can see the rich stack trace in the browser console/network tab.
                    $request->headers->set('Accept', 'text/html');
                }

                return null; // Fallback to default handler
            });
        }

        // Structured JSON error responses for API routes in production
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! config('app.debug') && ($request->is('api/*') || $request->expectsJson())) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'message' => $e->getMessage() ?: 'An unexpected error occurred.',
                    'code' => class_basename($e),
                    'errors' => method_exists($e, 'errors') ? $e->errors() : (object) [],
                ], $status);
            }

            return null;
        });
    })->create();
