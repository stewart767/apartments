<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDetail extends Model
{
    protected $table = 'tenant_details';

    protected $fillable = [
        'user_id',
        'national_id_passport',
        'emergency_contact',
        'is_student',
        'institution_id',
        'student_reg_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
