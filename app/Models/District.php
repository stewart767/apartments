<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['region_id', 'name'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
