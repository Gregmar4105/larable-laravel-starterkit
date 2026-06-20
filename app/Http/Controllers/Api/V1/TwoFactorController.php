<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConfirmTwoFactorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

/**
 * API V1 Two-Factor Authentication Controller
 *
 * Handles all 2FA operations: enable, confirm, disable, QR code, recovery codes.
 */
class TwoFactorController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     *
     * @group Two-Factor Authentication
     *
     * @authenticated
     *
     * @description Starts the 2FA setup process. Returns QR code SVG and secret key.
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
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
     *
     * @authenticated
     *
     * @bodyParam code string required A valid 6-digit TOTP code from the authenticator app.
     */
    public function confirm(ConfirmTwoFactorRequest $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {

        $confirm($request->user(), $request->code);

        return response()->json([
            'message' => 'Two-factor authentication confirmed and active.',
        ]);
    }

    /**
     * Disable two-factor authentication.
     *
     * @group Two-Factor Authentication
     *
     * @authenticated
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
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
     *
     * @authenticated
     */
    public function qrCode(Request $request): JsonResponse
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
     *
     * @authenticated
     */
    public function recoveryCodes(Request $request): JsonResponse
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
     *
     * @authenticated
     */
    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $generate($request->user());

        return response()->json([
            'recovery_codes' => json_decode(decrypt($request->user()->two_factor_recovery_codes), true),
        ]);
    }
}
