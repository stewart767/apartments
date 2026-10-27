<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_annual',
        'unit_limit',
        'property_limit',
        'staff_limit',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'unit_limit' => 'integer',
        'property_limit' => 'integer',
        'staff_limit' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function hasFeature(string $feature): bool
    {
        if (empty($this->features)) {
            return false;
        }
        return in_array($feature, $this->features) || in_array('*', $this->features);
    }

    public function getFormattedPriceMonthlyAttribute(): string
    {
        return 'TSh ' . number_format($this->price_monthly, 0);
    }

    public function getFormattedPriceAnnualAttribute(): string
    {
        return 'TSh ' . number_format($this->price_annual, 0);
    }

    public function getAnnualSavingsAttribute(): float
    {
        $twelveMonths = (float) $this->price_monthly * 12;
        $annual = (float) $this->price_annual;
        return max(0, $twelveMonths - $annual);
    }

    public function getFormattedAnnualSavingsAttribute(): string
    {
        return 'TSh ' . number_format($this->annual_savings, 0);
    }
}
