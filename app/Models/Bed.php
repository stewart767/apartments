<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bed extends Model
{
    use SoftDeletes;

    protected $fillable = ['room_id', 'bed_number', 'price', 'billing_period', 'status', 'current_tenant_id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function currentTenant()
    {
        return $this->belongsTo(User::class, 'current_tenant_id');
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
}
