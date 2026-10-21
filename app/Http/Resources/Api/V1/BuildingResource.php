<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'building_name' => $this->building_name,
            'building_code' => $this->building_code,
            'number_of_floors' => (int) $this->number_of_floors,
            'description' => $this->description,
            'status' => $this->status,
            'floors' => FloorResource::collection($this->whenLoaded('floors')),
        ];
    }
}
