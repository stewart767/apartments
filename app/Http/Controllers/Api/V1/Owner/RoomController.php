<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\RoomResource;
use App\Models\Room;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoomController extends ApiController
{
    /**
     * Add a room to a property/floor.
     */
    public function store(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        $request->validate([
            'room_number' => 'required|string|max:50',
            'room_type_id' => 'required|exists:room_types,id',
            'building_id' => 'nullable|exists:buildings,id',
            'floor_id' => 'nullable|exists:floors,id',
            'capacity' => 'required|integer|min:1',
            'rental_model' => 'required|in:per_bed,per_room,per_unit,entire_property,per_person',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:daily,weekly,monthly,semester,six_months,annual',
            'bathroom_type' => 'required|in:private,shared',
            'kitchen_type' => 'required|in:private,shared,none',
            'furnished_status' => 'required|in:unfurnished,semi_furnished,fully_furnished',
            'gender_accommodation' => 'required|in:any,male,female',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $room = Room::create(array_merge(
            $request->only([
                'room_number', 'room_type_id', 'building_id', 'floor_id',
                'capacity', 'rental_model', 'price', 'billing_period',
                'bathroom_type', 'kitchen_type', 'furnished_status',
                'gender_accommodation', 'description'
            ]),
            [
                'property_id' => $property->id,
                'occupancy' => 0,
                'status' => 'active',
            ]
        ));

        if ($request->filled('amenities')) {
            $room->amenities()->sync($request->input('amenities'));
        }

        return $this->successResponse(
            new RoomResource($room->load(['beds', 'amenities', 'roomType'])),
            'Room created successfully.',
            201
        );
    }

    /**
     * Update room details.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $room = Room::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($id);

        $request->validate([
            'room_number' => 'required|string|max:50',
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'required|integer|min:1',
            'rental_model' => 'required|in:per_bed,per_room,per_unit,entire_property,per_person',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:daily,weekly,monthly,semester,six_months,annual',
            'bathroom_type' => 'required|in:private,shared',
            'kitchen_type' => 'required|in:private,shared,none',
            'furnished_status' => 'required|in:unfurnished,semi_furnished,fully_furnished',
            'gender_accommodation' => 'required|in:any,male,female',
            'status' => 'required|in:active,maintenance,inactive',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $room->update($request->only([
            'room_number', 'room_type_id', 'capacity', 'rental_model',
            'price', 'billing_period', 'bathroom_type', 'kitchen_type',
            'furnished_status', 'gender_accommodation', 'status', 'description'
        ]));

        if ($request->has('amenities')) {
            $room->amenities()->sync($request->input('amenities', []));
        }

        return $this->successResponse(
            new RoomResource($room->fresh(['beds', 'amenities', 'roomType'])),
            'Room updated successfully.'
        );
    }
}
