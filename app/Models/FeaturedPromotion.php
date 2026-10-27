<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'organization_id',
        'tier',
        'duration_days',
        'amount',
        'currency',
        'starts_at',
        'expires_at',
        'status',
        'payment_reference',
        'receipt_path',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'duration_days' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->starts_at 
            && $this->starts_at->isPast() 
            && $this->expires_at 
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'TSh ' . number_format($this->amount, 0);
    }

    public function getTierNameAttribute(): string
    {
        return match($this->tier) {
            'standard' => '7-Day Featured Placement',
            'extended' => '14-Day Extended Promotion',
            'premium' => '30-Day Premium Campaign',
            default => 'Featured Listing',
        };
    }
}
