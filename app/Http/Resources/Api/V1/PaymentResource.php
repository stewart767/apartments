<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_reference' => $this->payment_reference,
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
            'contract_id' => $this->contract_id,
            'contract_number' => $this->contract?->contract_number,
            'invoice_id' => $this->invoice_id,
            'payment_type' => $this->payment_type,
            'amount' => (float) $this->amount,
            'due_date' => $this->due_date ? ($this->due_date instanceof \DateTimeInterface ? $this->due_date->toDateString() : substr((string)$this->due_date, 0, 10)) : null,
            'paid_date' => $this->paid_date ? ($this->paid_date instanceof \DateTimeInterface ? $this->paid_date->toDateString() : substr((string)$this->paid_date, 0, 10)) : null,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'receipt_url' => $this->receipt_path ? asset('storage/' . $this->receipt_path) : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
