<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'building_id',
        'floor_id',
        'room_number',
        'room_type_id',
        'capacity',
        'occupancy',
        'description',
        'gender_accommodation',
        'furnished_status',
        'bathroom_type',
        'kitchen_type',
        'price',
        'billing_period',
        'status',
        'rental_model',
    ];

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

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class, 'room_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function tenantAssets()
    {
        return $this->hasMany(TenantAsset::class);
    }

    public function propertyAssets()
    {
        return $this->hasMany(Asset::class);
    }
}
