<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            'application_id' => $this->application_id,
            'tenant_id' => $this->tenant_id,
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'name' => $this->tenant->name,
                    'phone' => $this->tenant->phone,
                    'email' => $this->tenant->email,
                ];
            }),
            'property_id' => $this->property_id,
            'property' => $this->whenLoaded('property', function () {
                return [
                    'id' => $this->property->id,
                    'property_name' => $this->property->property_name,
                    'address' => $this->property->address,
                    'owner' => $this->property->owner ? [
                        'id' => $this->property->owner->id,
                        'name' => $this->property->owner->name,
                        'phone' => $this->property->owner->phone,
                    ] : null,
                ];
            }),
            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id,
                    'room_number' => $this->room->room_number,
                ];
            }),
            'bed' => $this->whenLoaded('bed', function () {
                return [
                    'id' => $this->bed->id,
                    'bed_number' => $this->bed->bed_number,
                ];
            }),
            'start_date' => $this->start_date ? ($this->start_date instanceof \DateTimeInterface ? $this->start_date->toDateString() : substr((string)$this->start_date, 0, 10)) : null,
            'end_date' => $this->end_date ? ($this->end_date instanceof \DateTimeInterface ? $this->end_date->toDateString() : substr((string)$this->end_date, 0, 10)) : null,
            'rent_amount' => (float) $this->rent_amount,
            'deposit_amount' => (float) $this->deposit_amount,
            'billing_frequency' => $this->billing_frequency,
            'rules' => $this->rules,
            'terms_and_conditions' => $this->terms_and_conditions,
            'tenant_accepted' => (bool) $this->tenant_accepted,
            'tenant_accepted_at' => $this->tenant_accepted_at?->toIso8601String(),
            'owner_accepted' => (bool) $this->owner_accepted,
            'owner_accepted_at' => $this->owner_accepted_at?->toIso8601String(),
            'status' => $this->status,
            'pdf_download_url' => url("/api/v1/tenant/contracts/{$this->id}/pdf"),
            'check_in' => $this->whenLoaded('checkIn', function () {
                return [
                    'check_in_date' => $this->checkIn->check_in_date,
                    'deposit_received' => (float) $this->checkIn->deposit_received,
                    'room_condition_notes' => $this->checkIn->room_condition_notes,
                ];
            }),
            'check_out' => $this->whenLoaded('checkOut', function () {
                return [
                    'check_out_date' => $this->checkOut->check_out_date,
                    'damage_charges' => (float) $this->checkOut->damage_charges,
                    'deposit_refunded' => (float) $this->checkOut->deposit_refunded,
                    'outstanding_balance_settled' => (bool) $this->checkOut->outstanding_balance_settled,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
