<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Models\TenantDetail;
use App\Models\OwnerDetail;
use App\Models\Institution;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class RegisterController extends ApiController
{
    /**
     * Register a new user (Tenant or Property Owner).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $role = $request->input('role', 'tenant');

            // 1. Create Base User
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => $role,
                'status' => 'active',
                'phone' => $request->input('phone'),
                'whatsapp' => $request->input('whatsapp'),
                'gender' => $request->input('gender'),
                'date_of_birth' => $request->input('date_of_birth'),
                'fcm_token' => $request->input('fcm_token'),
            ]);

            // 2. Handle Tenant Profile details
            if ($role === 'tenant') {
                $institutionId = $request->input('institution_id');

                // Custom university/institution registration if provided
                if ($request->filled('custom_institution_name')) {
                    $inst = Institution::firstOrCreate([
                        'name' => $request->input('custom_institution_name'),
                    ]);
                    $institutionId = $inst->id;
                }

                TenantDetail::create([
                    'user_id' => $user->id,
                    'national_id_passport' => $request->input('national_id_passport'),
                    'emergency_contact' => $request->input('emergency_contact'),
                    'is_student' => (bool) $request->input('is_student', false),
                    'institution_id' => $institutionId,
                    'student_reg_number' => $request->input('student_reg_number'),
                ]);
            } elseif ($role === 'owner') {
                OwnerDetail::create([
                    'user_id' => $user->id,
                    'business_name' => $request->input('business_name', $user->name),
                    'verification_status' => 'pending',
                ]);
            }

            // 3. Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'user_registered',
                'model_type' => User::class,
                'model_id' => $user->id,
                'description' => "New {$role} user account created via mobile API.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // 4. Issue Sanctum token
            $deviceName = $request->input('device_name', 'Flutter Mobile App');
            $token = $user->createToken($deviceName)->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user->load(['tenantDetail.institution', 'ownerDetail'])),
            ], 'Account created successfully.', 201);
        });
    }
}
