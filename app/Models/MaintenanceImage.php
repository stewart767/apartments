<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceImage extends Model
{
    protected $fillable = ['maintenance_request_id', 'file_path'];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
