<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'asset_code',
        'asset_name',
        'category_id',
        'property_id',
        'building_id',
        'floor_id',
        'room_id',
        'bed_id',
        'description',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'condition',
        'status',
        'owner_id',
        'photos',
        'notes',
    ];

    protected $casts = [
        'photos' => 'array',
        'purchase_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function handovers()
    {
        return $this->hasMany(AssetHandoverRecord::class);
    }
}
