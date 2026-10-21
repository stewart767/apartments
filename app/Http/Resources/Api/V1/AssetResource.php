<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_code' => $this->asset_code,
            'asset_name' => $this->asset_name,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'property_id' => $this->property_id,
            'room' => $this->room ? [
                'id' => $this->room->id,
                'room_number' => $this->room->room_number,
            ] : null,
            'bed' => $this->bed ? [
                'id' => $this->bed->id,
                'bed_number' => $this->bed->bed_number,
            ] : null,
            'description' => $this->description,
            'serial_number' => $this->serial_number,
            'condition' => $this->condition,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
