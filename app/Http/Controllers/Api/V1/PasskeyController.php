<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API V1 Passkey Controller
 *
 * Handles WebAuthn passkey management: list, delete, and placeholder
 * endpoints for registration and authentication.
 *
 * Note: Passkey registration and authentication use Fortify's built-in
 * WebAuthn routes. The endpoints here are wrappers for API consistency
 * and will be fully implemented in a future release.
 */
class PasskeyController extends Controller
{
    /**
     * Get WebAuthn registration options for passkey creation.
     *
     * @group Passkeys
     *
     * @authenticated
     *
     * @response 501 {"message": "Not yet implemented. Use Fortify's built-in passkey registration endpoint at /passkeys/register.", "status": "not_implemented"}
     */
    public function registerOptions(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Not yet implemented. Use Fortify\'s built-in passkey registration endpoint at /passkeys/register.',
            'status' => 'not_implemented',
        ], 501);
    }

    /**
     * Register a new passkey.
     *
     * @group Passkeys
     *
     * @authenticated
     *
     * @response 501 {"message": "Not yet implemented. Use Fortify's built-in passkey registration flow.", "status": "not_implemented"}
     */
    public function register(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Not yet implemented. Use Fortify\'s built-in passkey registration flow.',
            'status' => 'not_implemented',
        ], 501);
    }

    /**
     * Get WebAuthn authentication challenge.
     *
     * @group Passkeys
     *
     * @response 501 {"message": "Not yet implemented. Use Fortify's built-in passkey authentication endpoint.", "status": "not_implemented"}
     */
    public function authenticateOptions(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Not yet implemented. Use Fortify\'s built-in passkey authentication endpoint.',
            'status' => 'not_implemented',
        ], 501);
    }

    /**
     * Verify passkey authentication response.
     *
     * @group Passkeys
     *
     * @response 501 {"message": "Not yet implemented. Use Fortify's built-in passkey authentication flow.", "status": "not_implemented"}
     */
    public function authenticate(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Not yet implemented. Use Fortify\'s built-in passkey authentication flow.',
            'status' => 'not_implemented',
        ], 501);
    }

    /**
     * List all registered passkeys for the authenticated user.
     *
     * @group Passkeys
     *
     * @authenticated
     */
    public function list(Request $request): JsonResponse
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
     *
     * @authenticated
     */
    public function delete(Request $request, string $passkey): JsonResponse
    {
        $request->user()->passkeys()->where('id', $passkey)->delete();

        return response()->json([
            'message' => 'Passkey deleted successfully.',
        ]);
    }
}
