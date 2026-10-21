<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_reference',
        'tenant_id',
        'property_id',
        'contract_id',
        'payment_type',
        'amount',
        'due_date',
        'paid_date',
        'payment_method',
        'status',
        'receipt_path',
        'notes',
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
}
