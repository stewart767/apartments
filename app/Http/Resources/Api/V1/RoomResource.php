<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'building_id' => $this->building_id,
            'floor_id' => $this->floor_id,
            'room_number' => $this->room_number,
            'room_type' => $this->roomType ? [
                'id' => $this->roomType->id,
                'name' => $this->roomType->name,
                'slug' => $this->roomType->slug,
            ] : null,
            'capacity' => (int) $this->capacity,
            'occupancy' => (int) $this->occupancy,
            'available_spots' => max(0, (int) $this->capacity - (int) $this->occupancy),
            'rental_model' => $this->rental_model,
            'gender_accommodation' => $this->gender_accommodation,
            'furnished_status' => $this->furnished_status,
            'bathroom_type' => $this->bathroom_type,
            'kitchen_type' => $this->kitchen_type,
            'price' => (float) $this->price,
            'billing_period' => $this->billing_period,
            'status' => $this->status,
            'description' => $this->description,
            'beds' => BedResource::collection($this->whenLoaded('beds')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
        ];
    }
}
