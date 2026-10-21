<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'bed_number' => $this->bed_number,
            'price' => (float) $this->price,
            'billing_period' => $this->billing_period,
            'status' => $this->status,
            'is_available' => $this->status === 'available' && is_null($this->current_tenant_id),
            'current_tenant_id' => $this->current_tenant_id,
            'current_tenant' => $this->whenLoaded('currentTenant', function () {
                return [
                    'id' => $this->currentTenant->id,
                    'name' => $this->currentTenant->name,
                ];
            }),
        ];
    }
}
