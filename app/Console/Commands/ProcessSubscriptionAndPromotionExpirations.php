<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\FeaturedPromotion;
use App\Models\Property;
use App\Notifications\HostelSystemNotification;
use Illuminate\Support\Facades\Cache;

class ProcessSubscriptionAndPromotionExpirations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reliance:process-expirations';

    /**
     * The console command description.
     */
    protected $description = 'Process expired SaaS subscriptions and featured listing promotions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SaaS expiration processing...');

        // 1. Process expired subscriptions
        $expiredSubs = Subscription::with('organization.owner')
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotNull('current_period_ends_at')
            ->where('current_period_ends_at', '<', now())
            ->get();

        $subCount = 0;
        foreach ($expiredSubs as $sub) {
            $sub->update(['status' => 'expired']);
            $subCount++;

            $owner = $sub->organization->owner ?? null;
            if ($owner) {
                $owner->notify(new HostelSystemNotification(
                    'Subscription Expired',
                    "Your {$sub->plan->name} subscription expired on {$sub->current_period_ends_at->format('M d, Y')}. Please renew your plan to continue adding properties.",
                    route('owner.subscription')
                ));
            }
        }
        $this->info("Expired {$subCount} subscription(s).");

        // 2. Process expired featured promotions
        $expiredPromos = FeaturedPromotion::with('property')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $promoCount = 0;
        $affectedProperties = [];

        foreach ($expiredPromos as $promo) {
            $promo->update(['status' => 'expired']);
            $promoCount++;
            $affectedProperties[$promo->property_id] = true;
        }

        // Check if affected properties have any other active promotions
        foreach (array_keys($affectedProperties) as $propId) {
            $hasActive = FeaturedPromotion::where('property_id', $propId)
                ->where('status', 'active')
                ->where('expires_at', '>=', now())
                ->exists();

            if (!$hasActive) {
                Property::where('id', $propId)->update(['is_featured' => false]);
            }

            Cache::forget("property_details_{$propId}");
        }

        if ($promoCount > 0) {
            Cache::forget('featured_properties');
        }

        $this->info("Expired {$promoCount} featured promotion(s). Cache purged.");
        return 0;
    }
}
