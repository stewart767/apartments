<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'tenant_id',
        'property_id',
        'contract_id',
        'cleanliness',
        'security',
        'water',
        'electricity',
        'management',
        'location',
        'value_for_money',
        'comment',
        'is_approved',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function getAverageRatingAttribute()
    {
        return round((
            $this->cleanliness +
            $this->security +
            $this->water +
            $this->electricity +
            $this->management +
            $this->location +
            $this->value_for_money
        ) / 7, 1);
    }
}
