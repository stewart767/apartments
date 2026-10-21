<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ProfileController extends ApiController
{
    /**
     * Get authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['tenantDetail.institution', 'ownerDetail']);
        return $this->successResponse(new UserResource($user), 'Profile retrieved successfully.');
    }

    /**
     * Update authenticated user profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            $user->update($request->only([
                'name', 'email', 'phone', 'whatsapp', 'gender', 'date_of_birth'
            ]));

            if ($user->isTenant()) {
                $detail = $user->tenantDetail()->firstOrCreate(['user_id' => $user->id]);
                $detail->update($request->only([
                    'national_id_passport',
                    'emergency_contact',
                    'is_student',
                    'institution_id',
                    'student_reg_number',
                ]));
            } elseif ($user->isOwner()) {
                $detail = $user->ownerDetail()->firstOrCreate(['user_id' => $user->id]);
                $detail->update($request->only([
                    'business_name',
                    'registration_number',
                    'tax_number',
                    'id_type',
                    'id_number',
                    'address',
                ]));
            }
        });

        return $this->successResponse(
            new UserResource($user->fresh(['tenantDetail.institution', 'ownerDetail'])),
            'Profile updated successfully.'
        );
    }

    /**
     * Upload profile avatar image.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return $this->successResponse([
                'avatar_url' => asset('storage/' . $path),
            ], 'Avatar updated successfully.');
        }

        return $this->errorResponse('Failed to upload avatar image.', 400);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return $this->errorResponse('Current password does not match.', 422, [
                'current_password' => ['The current password provided is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return $this->successResponse(null, 'Password updated successfully.');
    }
}
