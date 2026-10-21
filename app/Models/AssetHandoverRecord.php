<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetHandoverRecord extends Model
{
    protected $fillable = [
        'tenant_id',
        'property_id',
        'room_id',
        'bed_id',
        'asset_id',
        'condition_at_handover',
        'handover_date',
        'staff_id',
        'tenant_confirmed',
        'tenant_confirmed_at',
        'notes',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'tenant_confirmed' => 'boolean',
        'tenant_confirmed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function inspections()
    {
        return $this->hasMany(AssetInspection::class);
    }
}
