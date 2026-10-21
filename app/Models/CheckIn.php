<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $table = 'check_ins';

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'check_in_date',
        'deposit_received',
        'room_condition_notes',
        'inventory_json',
        'staff_id',
        'notes',
    ];

    protected $casts = [
        'inventory_json' => 'array',
        'check_in_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
