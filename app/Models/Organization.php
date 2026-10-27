<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'company_reg_number',
        'tax_id',
        'phone',
        'email',
        'address',
        'city',
        'country',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members()
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'organization_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trialing'])
            ->latestOfMany();
    }

    public function subscriptionInvoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function onboardingOrders()
    {
        return $this->hasMany(OnboardingOrder::class);
    }

    public function featuredPromotions()
    {
        return $this->hasMany(FeaturedPromotion::class);
    }

    /**
     * Get active or trialing subscription (or null if none/expired).
     */
    public function getActiveSubscriptionAttribute(): ?Subscription
    {
        return $this->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('current_period_ends_at')
                  ->orWhere('current_period_ends_at', '>=', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Calculate total rentable units across all organization properties
     * Consistent Unit-Counting Rule:
     * - entire_property: 1 unit per property
     * - per_room / per_unit: 1 unit per room
     * - per_bed: 1 unit per bed
     * - mixed: sum of private rooms + individual beds
     */
    public function getTotalUnitsCountAttribute(): int
    {
        $propertyIds = $this->properties()->pluck('id')->toArray();
        if (empty($propertyIds)) {
            // Also include properties owned by the owner_user_id directly if not yet linked
            $propertyIds = Property::where('owner_id', $this->owner_user_id)->pluck('id')->toArray();
        }

        if (empty($propertyIds)) {
            return 0;
        }

        $units = 0;
        $properties = Property::whereIn('id', $propertyIds)->with(['rooms.beds'])->get();

        foreach ($properties as $prop) {
            $structure = $prop->rent_structure;
            if ($structure === 'entire_property') {
                $units += 1;
            } elseif ($structure === 'per_bed') {
                $units += $prop->beds()->count();
            } elseif ($structure === 'per_room' || $structure === 'per_unit') {
                $units += $prop->rooms()->count();
            } else {
                // mixed
                $roomsCount = $prop->rooms()->count();
                $bedsCount = $prop->beds()->count();
                $units += max($roomsCount, $bedsCount, 1);
            }
        }

        return $units;
    }

    /**
     * Total properties count.
     */
    public function getTotalPropertiesCountAttribute(): int
    {
        $count = $this->properties()->count();
        if ($count === 0) {
            $count = Property::where('owner_id', $this->owner_user_id)->count();
        }
        return $count;
    }

    /**
     * Total staff members count.
     */
    public function getTotalStaffCountAttribute(): int
    {
        $propertyIds = $this->properties()->pluck('id')->toArray();
        if (empty($propertyIds)) {
            $propertyIds = Property::where('owner_id', $this->owner_user_id)->pluck('id')->toArray();
        }

        if (empty($propertyIds)) {
            return 0;
        }

        return \DB::table('property_user')
            ->whereIn('property_id', $propertyIds)
            ->distinct('user_id')
            ->count('user_id');
    }
}
