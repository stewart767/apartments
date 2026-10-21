<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\BuildingResource;
use App\Models\Building;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BuildingController extends ApiController
{
    /**
     * Add a building to a property.
     */
    public function store(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        $request->validate([
            'building_name' => 'required|string|max:255',
            'building_code' => 'nullable|string|max:50',
            'number_of_floors' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $building = Building::create([
            'property_id' => $property->id,
            'building_name' => $request->input('building_name'),
            'building_code' => $request->input('building_code'),
            'number_of_floors' => (int) $request->input('number_of_floors'),
            'description' => $request->input('description'),
            'status' => 'active',
        ]);

        return $this->successResponse(new BuildingResource($building), 'Building created successfully.', 201);
    }

    /**
     * Update a building.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $building = Building::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($id);

        $request->validate([
            'building_name' => 'required|string|max:255',
            'building_code' => 'nullable|string|max:50',
            'number_of_floors' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $building->update($request->only(['building_name', 'building_code', 'number_of_floors', 'description', 'status']));

        return $this->successResponse(new BuildingResource($building), 'Building updated successfully.');
    }
}
