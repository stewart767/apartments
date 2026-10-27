<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'organization_id',
        'service_tier',
        'amount',
        'currency',
        'status',
        'details',
        'payment_reference',
        'receipt_path',
        'paid_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'TSh ' . number_format($this->amount, 0);
    }

    public function getTierNameAttribute(): string
    {
        return match($this->service_tier) {
            'basic_setup' => 'Basic Landlord Setup',
            'hostel_training' => 'Hostel Setup & Staff Training',
            'multi_property' => 'Multi-Property Enterprise Onboarding',
            default => 'Custom Onboarding Service',
        };
    }
}
