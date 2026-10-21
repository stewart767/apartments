<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_number',
        'application_id',
        'tenant_id',
        'property_id',
        'building_id',
        'floor_id',
        'room_id',
        'bed_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'billing_frequency',
        'rules',
        'terms_and_conditions',
        'tenant_accepted',
        'tenant_accepted_at',
        'owner_accepted',
        'owner_accepted_at',
        'status',
        'pdf_path',
        'termination_reason',
        'terminated_at',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut()
    {
        return $this->hasOne(CheckOut::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
