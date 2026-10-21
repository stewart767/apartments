<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'tenant_id' => $this->tenant_id,
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'name' => $this->tenant->name,
                    'email' => $this->tenant->email,
                    'phone' => $this->tenant->phone,
                    'avatar' => $this->tenant->avatar ? asset('storage/' . $this->tenant->avatar) : null,
                    'tenant_detail' => $this->tenant->tenantDetail ? [
                        'is_student' => (bool) $this->tenant->tenantDetail->is_student,
                        'student_reg_number' => $this->tenant->tenantDetail->student_reg_number,
                        'institution' => $this->tenant->tenantDetail->institution?->name,
                    ] : null,
                ];
            }),
            'property_id' => $this->property_id,
            'property' => $this->whenLoaded('property', function () {
                return [
                    'id' => $this->property->id,
                    'property_name' => $this->property->property_name,
                    'address' => $this->property->address,
                    'rent_structure' => $this->property->rent_structure,
                    'primary_image' => $this->property->primaryImage ? asset('storage/' . $this->property->primaryImage->file_path) : null,
                ];
            }),
            'room_id' => $this->room_id,
            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id,
                    'room_number' => $this->room->room_number,
                    'capacity' => $this->room->capacity,
                ];
            }),
            'bed_id' => $this->bed_id,
            'bed' => $this->whenLoaded('bed', function () {
                return [
                    'id' => $this->bed->id,
                    'bed_number' => $this->bed->bed_number,
                ];
            }),
            'start_date' => $this->start_date ? ($this->start_date instanceof \DateTimeInterface ? $this->start_date->toDateString() : substr((string)$this->start_date, 0, 10)) : null,
            'end_date' => $this->end_date ? ($this->end_date instanceof \DateTimeInterface ? $this->end_date->toDateString() : substr((string)$this->end_date, 0, 10)) : null,
            'price' => (float) $this->price,
            'billing_period' => $this->billing_period,
            'status' => $this->status,
            'owner_notes' => $this->owner_notes,
            'tenant_notes' => $this->tenant_notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
