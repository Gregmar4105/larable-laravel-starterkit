<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PasskeyController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\TwoFactorController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api/v1/
| Sanctum token-based authentication is used for protected routes.
|
| Controllers:
|   - AuthController          → login, register, logout, CSRF
|   - PasswordResetController → forgot/reset password
|   - TwoFactorController     → 2FA enable, confirm, disable, QR, recovery
|   - PasskeyController       → passkey registration, authentication, list, delete
|   - UserController           → profile, password update
|
*/

// ─── Health Check ─────────────────────────────────────────────────────
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.v1.health');

// ─── Authentication (Public) ──────────────────────────────────────────
Route::prefix('auth')->name('api.v1.auth.')->middleware('throttle:login')->group(function () {
    // CSRF Cookie (for SPA authentication)
    Route::get('/csrf-cookie', [AuthController::class, 'csrfCookie'])
        ->name('csrf-cookie')
        ->withoutMiddleware('throttle:login');

    // Login & Register
    Route::post('/login', [AuthController::class, 'login'])
        ->name('login');
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register');

    // Password Reset
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
        ->name('forgot-password');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('reset-password');

    // Passkeys (Public challenge)
    Route::post('/passkeys/authenticate-options', [PasskeyController::class, 'authenticateOptions'])
        ->name('passkeys.authenticate-options');
    Route::post('/passkeys/authenticate', [PasskeyController::class, 'authenticate'])
        ->name('passkeys.authenticate');
});

// ─── Authenticated Routes ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('api.v1.auth.logout');

    // ─── Two-Factor Authentication ────────────────────────────────
    Route::prefix('auth/two-factor')->name('api.v1.auth.2fa.')->group(function () {
        Route::post('/enable', [TwoFactorController::class, 'enable'])
            ->name('enable');
        Route::post('/confirm', [TwoFactorController::class, 'confirm'])
            ->name('confirm');
        Route::delete('/disable', [TwoFactorController::class, 'disable'])
            ->name('disable');
        Route::get('/qr-code', [TwoFactorController::class, 'qrCode'])
            ->name('qr-code');
        Route::get('/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])
            ->name('recovery-codes');
        Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
            ->name('recovery-codes.regenerate');
    });

    // ─── Passkeys (Authenticated) ─────────────────────────────────
    Route::prefix('auth/passkeys')->name('api.v1.auth.passkeys.')->group(function () {
        Route::post('/register-options', [PasskeyController::class, 'registerOptions'])
            ->name('register-options');
        Route::post('/register', [PasskeyController::class, 'register'])
            ->name('register');
        Route::get('/', [PasskeyController::class, 'list'])
            ->name('list');
        Route::delete('/{passkey}', [PasskeyController::class, 'delete'])
            ->name('delete');
    });

    // ─── User Profile ─────────────────────────────────────────────
    Route::get('/user', [UserController::class, 'show'])
        ->name('api.v1.user.show');
    Route::put('/user/profile', [UserController::class, 'updateProfile'])
        ->name('api.v1.user.update-profile');
    Route::put('/user/password', [UserController::class, 'updatePassword'])
        ->name('api.v1.user.update-password');
});
