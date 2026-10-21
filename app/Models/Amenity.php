<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name', 'icon', 'category'];

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'amenity_property');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'amenity_room');
    }
}
