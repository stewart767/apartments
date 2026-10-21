<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FloorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'floor_number_name' => $this->floor_number_name,
            'description' => $this->description,
            'status' => $this->status,
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
        ];
    }
}
