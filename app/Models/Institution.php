<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = ['name', 'description', 'address', 'latitude', 'longitude'];

    public function properties()
    {
        return $this->hasMany(Property::class, 'nearby_institution_id');
    }

    public function tenants()
    {
        return $this->hasMany(TenantDetail::class);
    }
}
