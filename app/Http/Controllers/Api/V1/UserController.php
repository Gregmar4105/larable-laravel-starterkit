<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API V1 User Controller
 *
 * Handles user profile operations: view, update profile, change password.
 */
class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     *
     * @group User
     * @authenticated
     * @description Returns the current user's profile data.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Update the authenticated user's profile information.
     *
     * @group User
     * @authenticated
     *
     * @bodyParam name string required The user's name. Example: Jane Doe
     * @bodyParam email string required The user's email. Example: jane@example.com
     */
    public function updateProfile(Request $request, UpdateUserProfileInformation $updater): JsonResponse
    {
        $updater->update($request->user(), $request->all());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($request->user()->fresh()),
        ]);
    }

    /**
     * Update the authenticated user's password.
     *
     * @group User
     * @authenticated
     *
     * @bodyParam current_password string required The user's current password.
     * @bodyParam password string required The new password.
     * @bodyParam password_confirmation string required Must match password.
     */
    public function updatePassword(Request $request, UpdateUserPassword $updater): JsonResponse
    {
        $updater->update($request->user(), $request->all());

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
