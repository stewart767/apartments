<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerDetail extends Model
{
    protected $fillable = [
        'user_id',
        'full_legal_name',
        'nida',
        'nationality',
        'phone',
        'whatsapp',
        'email',
        'region_id',
        'district_id',
        'ward_id',
        'street_address',
        'profile_photo',
        'verification_status',
        'id_document_path',
        'supporting_document_path',
        'rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->verification_status === 'suspended';
    }
}
