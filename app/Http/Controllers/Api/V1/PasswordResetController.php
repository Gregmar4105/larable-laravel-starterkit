<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * API V1 Password Reset Controller
 *
 * Handles forgot password and reset password flows.
 */
class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given email.
     *
     * @group Password Reset
     *
     * @description Send a password reset email. The email contains a link to the frontend reset page.
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {

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

    /**
     * Reset the user's password using a valid reset token.
     *
     * @group Password Reset
     *
     * @description Complete the password reset using the token from the email link.
     *
     * @bodyParam token string required The reset token from the email.
     * @bodyParam email string required The user's email.
     * @bodyParam password string required The new password.
     * @bodyParam password_confirmation string required Must match password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {

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
}
