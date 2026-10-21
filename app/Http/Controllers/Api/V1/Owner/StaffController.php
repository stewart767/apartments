<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class StaffController extends ApiController
{
    /**
     * List assigned staff members for a property.
     */
    public function index(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);
        $staff = $property->managers()->withPivot('role')->get(['users.id', 'users.name', 'users.email', 'users.phone']);

        return $this->successResponse($staff, 'Property staff members retrieved.');
    }

    /**
     * Assign a staff member (Manager, Receptionist, Agent, Caretaker, Accountant) to property.
     */
    public function store(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:manager,receptionist,agent,caretaker,accountant',
        ]);

        $staffUser = User::where('email', $request->input('email'))->firstOrFail();

        // Attach or update role
        DB::table('property_user')->updateOrInsert(
            [
                'property_id' => $property->id,
                'user_id' => $staffUser->id,
            ],
            [
                'role' => $request->input('role'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return $this->successResponse(null, "Staff member {$staffUser->name} assigned to property as {$request->input('role')}.", 201);
    }

    /**
     * Remove staff member from property.
     */
    public function destroy(Request $request, $propertyId, $userId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        DB::table('property_user')
            ->where('property_id', $property->id)
            ->where('user_id', $userId)
            ->delete();

        return $this->successResponse(null, 'Staff member removed from property.');
    }
}
