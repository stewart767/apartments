<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\BedResource;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BedController extends ApiController
{
    /**
     * Add a bed to a room.
     */
    public function store(Request $request, $roomId): JsonResponse
    {
        $room = Room::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($roomId);

        $request->validate([
            'bed_number' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|in:daily,weekly,monthly,semester,six_months,annual',
        ]);

        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_number' => $request->input('bed_number'),
            'price' => $request->input('price', $room->price),
            'billing_period' => $request->input('billing_period', $room->billing_period),
            'status' => 'available',
        ]);

        return $this->successResponse(new BedResource($bed), 'Bed created successfully.', 201);
    }

    /**
     * Update bed details.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $bed = Bed::whereHas('room.property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($id);

        $request->validate([
            'bed_number' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|in:daily,weekly,monthly,semester,six_months,annual',
            'status' => 'required|in:available,reserved,occupied,maintenance,unavailable',
        ]);

        $bed->update($request->only(['bed_number', 'price', 'billing_period', 'status']));

        return $this->successResponse(new BedResource($bed), 'Bed updated successfully.');
    }
}
