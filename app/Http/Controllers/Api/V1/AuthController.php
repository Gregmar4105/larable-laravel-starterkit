<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

/**
 * API V1 Authentication Controller
 *
 * Handles all authentication flows for the decoupled React frontend:
 * login, register, logout, password reset, 2FA, and passkeys.
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
     * @description Login with email/password. Supports remember_me for extended sessions.
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     * @bodyParam password string required The user's password.
     * @bodyParam remember_me boolean optional Keep the session alive longer.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember_me' => ['boolean'],
        ]);

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
     * @description Create a new user account with name, email, and password.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The password (min 8 chars).
     * @bodyParam password_confirmation string required Must match password.
     */
    public function register(Request $request, CreateNewUser $creator): JsonResponse
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
     * @authenticated
     * @description Logout by revoking the current Bearer token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    // ─── Forgot Password ─────────────────────────────────────────

    /**
     * Send a password reset link to the given email.
     *
     * @group Password Reset
     * @description Send a password reset email. The email contains a link to the frontend reset page.
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    // ─── Reset Password ──────────────────────────────────────────

    /**
     * Reset the user's password using a valid reset token.
     *
     * @group Password Reset
     * @description Complete the password reset using the token from the email link.
     *
     * @bodyParam token string required The reset token from the email.
     * @bodyParam email string required The user's email.
     * @bodyParam password string required The new password.
     * @bodyParam password_confirmation string required Must match password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    // ─── Two-Factor Authentication ────────────────────────────────

    /**
     * Enable two-factor authentication for the user.
     *
     * @group Two-Factor Authentication
     * @authenticated
     * @description Starts the 2FA setup process. Returns QR code SVG and secret key.
     */
    public function enableTwoFactor(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
    {
        $enable($request->user(), false);

        return response()->json([
            'message' => 'Two-factor authentication enabled. Please confirm with a code.',
            'qr_code' => $request->user()->twoFactorQrCodeSvg(),
            'secret' => decrypt($request->user()->two_factor_secret),
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true),
        ]);
    }

    /**
     * Confirm 2FA setup with a valid TOTP code.
     *
     * @group Two-Factor Authentication
     * @authenticated
     *
     * @bodyParam code string required A valid 6-digit TOTP code from the authenticator app.
     */
    public function confirmTwoFactor(Request $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $confirm($request->user(), $request->code);

        return response()->json([
            'message' => 'Two-factor authentication confirmed and active.',
        ]);
    }

    /**
     * Disable two-factor authentication.
     *
     * @group Two-Factor Authentication
     * @authenticated
     */
    public function disableTwoFactor(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
    {
        $disable($request->user());

        return response()->json([
            'message' => 'Two-factor authentication disabled.',
        ]);
    }

    /**
     * Get the QR code SVG for 2FA setup.
     *
     * @group Two-Factor Authentication
     * @authenticated
     */
    public function twoFactorQrCode(Request $request): JsonResponse
    {
        if (! $request->user()->two_factor_secret) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        return response()->json([
            'svg' => $request->user()->twoFactorQrCodeSvg(),
            'url' => $request->user()->twoFactorQrCodeUrl(),
        ]);
    }

    /**
     * Get current recovery codes.
     *
     * @group Two-Factor Authentication
     * @authenticated
     */
    public function recoveryCodesGet(Request $request): JsonResponse
    {
        if (! $request->user()->two_factor_secret) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        return response()->json([
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true),
        ]);
    }

    /**
     * Regenerate recovery codes.
     *
     * @group Two-Factor Authentication
     * @authenticated
     */
    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $generate($request->user());

        return response()->json([
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true),
        ]);
    }

    // ─── Passkeys ─────────────────────────────────────────────────

    /**
     * Get WebAuthn registration options for passkey creation.
     *
     * @group Passkeys
     * @authenticated
     */
    public function passkeyRegisterOptions(Request $request): JsonResponse
    {
        // Passkey registration options are handled by Fortify's built-in routes
        // This is a wrapper for API consistency
        return response()->json([
            'message' => 'Use the Fortify passkey registration endpoint.',
            'endpoint' => '/passkeys/register',
        ]);
    }

    /**
     * Register a new passkey.
     *
     * @group Passkeys
     * @authenticated
     */
    public function passkeyRegister(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Use the Fortify passkey registration flow.',
        ]);
    }

    /**
     * Get WebAuthn authentication challenge.
     *
     * @group Passkeys
     */
    public function passkeyAuthenticateOptions(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Use the Fortify passkey authentication endpoint.',
        ]);
    }

    /**
     * Verify passkey authentication response.
     *
     * @group Passkeys
     */
    public function passkeyAuthenticate(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Use the Fortify passkey authentication flow.',
        ]);
    }

    /**
     * List all registered passkeys for the authenticated user.
     *
     * @group Passkeys
     * @authenticated
     */
    public function passkeysList(Request $request): JsonResponse
    {
        $passkeys = $request->user()->passkeys ?? collect();

        return response()->json([
            'passkeys' => $passkeys,
        ]);
    }

    /**
     * Delete a registered passkey.
     *
     * @group Passkeys
     * @authenticated
     */
    public function passkeyDelete(Request $request, string $passkey): JsonResponse
    {
        $request->user()->passkeys()->where('id', $passkey)->delete();

        return response()->json([
            'message' => 'Passkey deleted successfully.',
        ]);
    }
}
