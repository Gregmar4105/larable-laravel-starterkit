<?php

use App\Http\Controllers\Api\V1\AuthController;
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
*/

// ─── Health Check ─────────────────────────────────────────────────────
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
        'debug' => config('app.debug'),
    ]);
})->name('api.v1.health');

// ─── Authentication (Public) ──────────────────────────────────────────
Route::prefix('auth')->name('api.v1.auth.')->group(function () {
    // CSRF Cookie (for SPA authentication)
    Route::get('/csrf-cookie', [AuthController::class, 'csrfCookie'])
        ->name('csrf-cookie');

    // Login & Register
    Route::post('/login', [AuthController::class, 'login'])
        ->name('login');
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register');

    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('reset-password');

    // Passkeys (Public challenge)
    Route::post('/passkeys/authenticate-options', [AuthController::class, 'passkeyAuthenticateOptions'])
        ->name('passkeys.authenticate-options');
    Route::post('/passkeys/authenticate', [AuthController::class, 'passkeyAuthenticate'])
        ->name('passkeys.authenticate');
});

// ─── Authenticated Routes ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('api.v1.auth.logout');

    // ─── Two-Factor Authentication ────────────────────────────────
    Route::prefix('auth/two-factor')->name('api.v1.auth.2fa.')->group(function () {
        Route::post('/enable', [AuthController::class, 'enableTwoFactor'])
            ->name('enable');
        Route::post('/confirm', [AuthController::class, 'confirmTwoFactor'])
            ->name('confirm');
        Route::delete('/disable', [AuthController::class, 'disableTwoFactor'])
            ->name('disable');
        Route::get('/qr-code', [AuthController::class, 'twoFactorQrCode'])
            ->name('qr-code');
        Route::get('/recovery-codes', [AuthController::class, 'recoveryCodesGet'])
            ->name('recovery-codes');
        Route::post('/recovery-codes', [AuthController::class, 'regenerateRecoveryCodes'])
            ->name('recovery-codes.regenerate');
    });

    // ─── Passkeys (Authenticated) ─────────────────────────────────
    Route::prefix('auth/passkeys')->name('api.v1.auth.passkeys.')->group(function () {
        Route::post('/register-options', [AuthController::class, 'passkeyRegisterOptions'])
            ->name('register-options');
        Route::post('/register', [AuthController::class, 'passkeyRegister'])
            ->name('register');
        Route::get('/', [AuthController::class, 'passkeysList'])
            ->name('list');
        Route::delete('/{passkey}', [AuthController::class, 'passkeyDelete'])
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
