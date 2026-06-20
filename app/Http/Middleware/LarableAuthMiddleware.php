<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Larable Auth Middleware
 *
 * Protects the /larable backend GUI with a simple password gate.
 * Set LARABLE_PASSWORD in .env to enable protection.
 *
 * If no password is set, access is denied entirely.
 * Once authenticated, the password is stored in the session.
 */
class LarableAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $password = config('app.larable_password');

        // If no password is configured, deny access entirely
        if (empty($password)) {
            abort(403, 'Larable GUI is disabled. Set LARABLE_PASSWORD in your .env file.');
        }

        // Check if already authenticated via session
        if ($request->session()->get('larable_authenticated') === true) {
            return $next($request);
        }

        // Handle password submission
        if ($request->isMethod('POST') && $request->has('larable_password')) {
            if ($request->input('larable_password') === $password) {
                $request->session()->put('larable_authenticated', true);

                return redirect()->intended(route('larable.dashboard'));
            }

            return $this->renderLoginPage('Invalid password. Please try again.');
        }

        // Show login page
        return $this->renderLoginPage();
    }

    /**
     * Render a simple, styled password prompt page.
     */
    protected function renderLoginPage(?string $error = null): Response
    {
        $errorHtml = $error
            ? '<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:0.75rem 1rem;border-radius:6px;font-size:0.875rem;margin-bottom:1rem;">'.e($error).'</div>'
            : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Larable — Authentication Required</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: #09090b;
            color: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            width: 100%;
            max-width: 400px;
            background: #18181b;
            border: 1px solid #27272a;
            border-radius: 8px;
            padding: 2.5rem;
        }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; }
        .header p { color: #71717a; font-size: 0.875rem; margin-top: 0.5rem; }
        .badge {
            display: inline-block;
            background: rgba(250,250,250,0.05);
            border: 1px solid #27272a;
            border-radius: 4px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            color: #a1a1aa;
            margin-bottom: 1rem;
        }
        label { display: block; font-size: 0.8125rem; font-weight: 500; color: #a1a1aa; margin-bottom: 0.375rem; }
        input[type="password"] {
            width: 100%;
            padding: 0.5rem 0.75rem;
            background: transparent;
            border: 1px solid #27272a;
            border-radius: 6px;
            color: #fafafa;
            font-size: 0.875rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s ease;
        }
        input[type="password"]:focus { border-color: #fafafa; box-shadow: 0 0 0 1px #fafafa; }
        button {
            width: 100%;
            margin-top: 1.25rem;
            padding: 0.5rem 1rem;
            background: #fafafa;
            color: #09090b;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        button:hover { background: #e4e4e7; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="badge">🔒 Protected</div>
            <h1>Larable</h1>
            <p>Enter the dashboard password to continue.</p>
        </div>
        {$errorHtml}
        <form method="POST">
            <input type="hidden" name="_token" value="{$this->csrfToken()}">
            <div>
                <label for="larable_password">Password</label>
                <input type="password" id="larable_password" name="larable_password" placeholder="Enter password" autofocus required>
            </div>
            <button type="submit">Authenticate</button>
        </form>
    </div>
</body>
</html>
HTML;

        return response($html, 401);
    }

    /**
     * Get the current CSRF token.
     */
    protected function csrfToken(): string
    {
        return csrf_token();
    }
}
