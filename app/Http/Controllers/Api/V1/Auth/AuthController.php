<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class AuthController extends ApiController
{
    /**
     * Authenticate user & issue Sanctum API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with(['tenantDetail.institution', 'ownerDetail'])
            ->where('email', $request->input('email'))
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return $this->errorResponse('Invalid credentials provided.', 401, [
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (!$user->isActive()) {
            return $this->errorResponse('Your account has been suspended or deactivated. Please contact support.', 403);
        }

        // Save FCM Device Token if passed
        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->input('fcm_token')]);
        }

        $deviceName = $request->input('device_name', 'Flutter Mobile App');
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Login successful.');
    }

    /**
     * Revoke active API token on logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Successfully logged out.');
    }

    /**
     * Register or update mobile FCM push notification token.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update(['fcm_token' => $request->input('fcm_token')]);

        return $this->successResponse(null, 'FCM device token registered successfully.');
    }
}
