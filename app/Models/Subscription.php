<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'plan_id',
        'billing_interval',
        'status',
        'price',
        'currency',
        'starts_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'trial_ends_at',
        'cancelled_at',
        'ended_at',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active' && $this->status !== 'trialing') {
            return false;
        }

        if ($this->current_period_ends_at && $this->current_period_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && ($this->trial_ends_at ? $this->trial_ends_at->isFuture() : true);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->current_period_ends_at && $this->current_period_ends_at->isPast());
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || !is_null($this->cancelled_at);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->current_period_ends_at) {
            return 0;
        }
        return (int) max(0, now()->diffInDays($this->current_period_ends_at, false));
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'TSh ' . number_format($this->price, 0);
    }
}
