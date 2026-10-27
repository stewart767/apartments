<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'organization_id',
        'subscription_id',
        'plan_id',
        'amount',
        'currency',
        'billing_interval',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'receipt_path',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'TSh ' . number_format($this->amount, 0);
    }
}
