<?php

use App\Http\Middleware\ApiVersionMiddleware;
use App\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'idempotency' => IdempotencyMiddleware::class,
            'api.version' => ApiVersionMiddleware::class,
        ]);

        // Configure Sanctum stateful middleware for SPA auth
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (config('app.debug')) {
            $exceptions->render(function (\Throwable $e, Request $request) {
                $exemptions = [
                    \Illuminate\Validation\ValidationException::class,
                    \Illuminate\Auth\AuthenticationException::class,
                    \Illuminate\Auth\Access\AuthorizationException::class,
                    \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface::class,
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
        } else {
            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
            );
        }
    })->create();

