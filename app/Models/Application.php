<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_number',
        'tenant_id',
        'property_id',
        'room_id',
        'bed_id',
        'start_date',
        'end_date',
        'price',
        'billing_period',
        'status',
        'owner_notes',
        'tenant_notes',
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

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
}
