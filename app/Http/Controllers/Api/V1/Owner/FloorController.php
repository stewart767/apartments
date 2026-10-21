<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\FloorResource;
use App\Models\Floor;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FloorController extends ApiController
{
    /**
     * Add a floor to a building.
     */
    public function store(Request $request, $buildingId): JsonResponse
    {
        $building = Building::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($buildingId);

        $request->validate([
            'floor_number_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $floor = Floor::create([
            'building_id' => $building->id,
            'floor_number_name' => $request->input('floor_number_name'),
            'description' => $request->input('description'),
            'status' => 'active',
        ]);

        return $this->successResponse(new FloorResource($floor), 'Floor created successfully.', 201);
    }
}
