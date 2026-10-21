<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'tenant_id' => $this->tenant_id,
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'name' => $this->tenant->name,
                    'phone' => $this->tenant->phone,
                ];
            }),
            'property_id' => $this->property_id,
            'property_name' => $this->property?->property_name,
            'room' => $this->room ? [
                'id' => $this->room->id,
                'room_number' => $this->room->room_number,
            ] : null,
            'bed' => $this->bed ? [
                'id' => $this->bed->id,
                'bed_number' => $this->bed->bed_number,
            ] : null,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assignedTo ? [
                'id' => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
                'role' => $this->assignedTo->role,
            ] : null,
            'resolution_notes' => $this->resolution_notes,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'images' => $this->images ? $this->images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->file_path),
                ];
            }) : [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
