<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'phone' => $this->owner->phone,
                    'email' => $this->owner->email,
                ];
            }),
            'property_name' => $this->property_name,
            'property_type' => $this->propertyType ? [
                'id' => $this->propertyType->id,
                'name' => $this->propertyType->name,
                'slug' => $this->propertyType->slug,
            ] : null,
            'description' => $this->description,
            'location' => [
                'region' => $this->region ? ['id' => $this->region->id, 'name' => $this->region->name] : null,
                'district' => $this->district ? ['id' => $this->district->id, 'name' => $this->district->name] : null,
                'ward' => $this->ward ? ['id' => $this->ward->id, 'name' => $this->ward->name] : null,
                'street' => $this->street,
                'address' => $this->address,
                'latitude' => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            'nearby_institution' => $this->institution ? [
                'id' => $this->institution->id,
                'name' => $this->institution->name,
                'distance_km' => $this->distance_from_institution ? (float) $this->distance_from_institution : null,
            ] : null,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'is_featured' => (bool) $this->is_featured,
            'rent_structure' => $this->rent_structure,
            'price' => $this->price ? (float) $this->price : null,
            'billing_period' => $this->billing_period,
            'primary_image' => $this->primaryImage ? asset('storage/' . $this->primaryImage->file_path) : null,
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->file_path),
                        'is_primary' => (bool) $img->is_primary,
                        'room_id' => $img->room_id,
                        'building_id' => $img->building_id,
                    ];
                });
            }),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'buildings' => BuildingResource::collection($this->whenLoaded('buildings')),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'availability_summary' => $this->when(isset($this->availability_summary), $this->availability_summary),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
