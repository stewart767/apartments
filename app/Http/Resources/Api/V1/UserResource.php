<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth ? ($this->date_of_birth instanceof \DateTimeInterface ? $this->date_of_birth->toDateString() : substr((string)$this->date_of_birth, 0, 10)) : null,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'tenant_detail' => $this->whenLoaded('tenantDetail', function () {
                return [
                    'national_id_passport' => $this->tenantDetail->national_id_passport,
                    'emergency_contact' => $this->tenantDetail->emergency_contact,
                    'is_student' => (bool) $this->tenantDetail->is_student,
                    'student_reg_number' => $this->tenantDetail->student_reg_number,
                    'institution' => $this->tenantDetail->institution ? [
                        'id' => $this->tenantDetail->institution->id,
                        'name' => $this->tenantDetail->institution->name,
                    ] : null,
                ];
            }),
            'owner_detail' => $this->whenLoaded('ownerDetail', function () {
                return [
                    'business_name' => $this->ownerDetail->business_name,
                    'registration_number' => $this->ownerDetail->registration_number,
                    'tax_number' => $this->ownerDetail->tax_number,
                    'id_type' => $this->ownerDetail->id_type,
                    'id_number' => $this->ownerDetail->id_number,
                    'address' => $this->ownerDetail->address,
                    'verification_status' => $this->ownerDetail->verification_status,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
