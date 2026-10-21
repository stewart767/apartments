<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant' => $this->tenant ? [
                'id' => $this->tenant->id,
                'name' => $this->tenant->name,
                'avatar' => $this->tenant->avatar ? asset('storage/' . $this->tenant->avatar) : null,
            ] : null,
            'cleanliness' => (int) $this->cleanliness,
            'security' => (int) $this->security,
            'water' => (int) $this->water,
            'electricity' => (int) $this->electricity,
            'management' => (int) $this->management,
            'location' => (int) $this->location,
            'value_for_money' => (int) $this->value_for_money,
            'average_rating' => round(($this->cleanliness + $this->security + $this->water + $this->electricity + $this->management + $this->location + $this->value_for_money) / 7, 1),
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
