<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API V1 Authentication Controller
 *
 * Handles core authentication flows for the decoupled React frontend:
 * login, register, logout, and CSRF cookie.
 *
 * Password reset, 2FA, and passkeys are handled by their dedicated controllers.
 *
 * All responses are JSON. No redirects, no views.
 */
class AuthController extends Controller
{
    // ─── CSRF Cookie (SPA Auth) ───────────────────────────────────

    /**
     * Return CSRF cookie for Sanctum SPA authentication.
     *
     * @group Authentication
     *
     * @description Initialize CSRF protection for SPA. Call this before login/register.
     */
    public function csrfCookie(Request $request): JsonResponse
    {
        return response()->json(['message' => 'CSRF cookie set']);
    }

    // ─── Login ────────────────────────────────────────────────────

    /**
     * Authenticate a user and return a Sanctum token.
     *
     * @group Authentication
     *
     * @description Login with email/password. Supports remember_me for extended sessions.
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     * @bodyParam password string required The user's password.
     * @bodyParam remember_me boolean optional Keep the session alive longer.
     */
    public function login(LoginRequest $request): JsonResponse
    {

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            return response()->json([
                'two_factor' => true,
                'message' => 'Two-factor authentication is required.',
            ], 200);
        }

        // Create token with appropriate expiry
        $tokenName = 'api-token';
        $abilities = ['*'];

        if ($request->remember_me) {
            $token = $user->createToken($tokenName, $abilities, now()->addDays(30));
        } else {
            $token = $user->createToken($tokenName, $abilities, now()->addHours(24));
        }

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ], 200);
    }

    // ─── Register ─────────────────────────────────────────────────

    /**
     * Register a new user account.
     *
     * @group Authentication
     *
     * @description Create a new user account with name, email, and password.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The password (min 8 chars).
     * @bodyParam password_confirmation string required Must match password.
     */
    public function register(RegisterRequest $request, CreateNewUser $creator): JsonResponse
    {
        $user = $creator->create($request->all());

        $token = $user->createToken('api-token', ['*'], now()->addHours(24));

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ], 201);
    }

    // ─── Logout ───────────────────────────────────────────────────

    /**
     * Revoke the current access token.
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @description Logout by revoking the current Bearer token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
