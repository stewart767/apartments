<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInspection extends Model
{
    protected $fillable = [
        'asset_handover_record_id',
        'original_condition',
        'current_condition',
        'damage_charges',
        'notes',
        'staff_id',
        'inspection_date',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function handoverRecord()
    {
        return $this->belongsTo(AssetHandoverRecord::class, 'asset_handover_record_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
