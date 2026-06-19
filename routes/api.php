<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Larable API with proper versioning. Each version has its own route file
| under routes/api/. The IdempotencyMiddleware and ApiVersionMiddleware
| are applied to all API routes.
|
| Versioning Pattern:
|   /api/v1/* → routes/api/v1.php
|   /api/v2/* → routes/api/v2.php (future)
|
*/

// ─── API v1 ───────────────────────────────────────────────────────────
Route::prefix('v1')
    ->middleware(['api.version:v1', 'idempotency'])
    ->group(base_path('routes/api/v1.php'));
