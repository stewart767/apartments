<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'contract_id' => $this->contract_id,
            'property_id' => $this->property_id,
            'property_name' => $this->property?->property_name,
            'tenant_id' => $this->tenant_id,
            'invoice_type' => $this->invoice_type,
            'amount' => (float) $this->amount,
            'due_date' => $this->due_date ? ($this->due_date instanceof \DateTimeInterface ? $this->due_date->toDateString() : substr((string)$this->due_date, 0, 10)) : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
