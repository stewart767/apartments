<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAsset extends Model
{
    protected $fillable = [
        'tenant_id',
        'asset_name',
        'category',
        'serial_number',
        'description',
        'condition',
        'photo_path',
        'registered_date',
        'notes',
        'room_id',
    ];

    protected $casts = [
        'registered_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
