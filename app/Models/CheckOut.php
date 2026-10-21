<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    protected $table = 'check_outs';

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'check_out_date',
        'room_condition_notes',
        'damage_charges',
        'deposit_refunded',
        'outstanding_balance_settled',
        'staff_id',
        'notes',
    ];

    protected $casts = [
        'check_out_date' => 'date',
        'outstanding_balance_settled' => 'boolean',
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
