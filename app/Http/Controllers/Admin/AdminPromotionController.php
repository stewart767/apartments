<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedPromotion;
use App\Models\Property;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminPromotionController extends Controller
{
    /**
     * List all featured listing promotions across the platform.
     */
    public function index(Request $request)
    {
        $query = FeaturedPromotion::with(['property.owner', 'organization', 'verifier'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $promotions = $query->paginate(15);

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Verify payment and activate featured promotion.
     */
    public function verify(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $promotion = FeaturedPromotion::with('property')->findOrFail($id);

        if ($request->input('action') === 'approve') {
            $durationDays = $promotion->duration_days ?: 7;
            $startsAt = now();
            $expiresAt = now()->addDays($durationDays);

            $promotion->update([
                'status' => 'active',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Update property
            if ($promotion->property) {
                $promotion->property->update(['is_featured' => true]);
            }

            // Invalidate featured cache
            Cache::forget('featured_properties');
            Cache::forget("property_details_{$promotion->property_id}");

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'featured_promotion_activated',
                'model_type' => FeaturedPromotion::class,
                'model_id' => $promotion->id,
                'description' => "Approved {$promotion->tier} featured promotion for {$promotion->property->property_name} until {$expiresAt->format('M d, Y')}.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', "Promotion activated for {$promotion->property->property_name} for {$durationDays} days!");
        } else {
            $promotion->update([
                'status' => 'cancelled',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'featured_promotion_rejected',
                'model_type' => FeaturedPromotion::class,
                'model_id' => $promotion->id,
                'description' => "Rejected featured promotion order #{$promotion->id}.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Promotion order was rejected.');
        }
    }

    /**
     * Manually expire or deactivate a promotion.
     */
    public function expire($id)
    {
        $promotion = FeaturedPromotion::with('property')->findOrFail($id);

        $promotion->update([
            'status' => 'expired',
            'expires_at' => now(),
        ]);

        // Check if property has any other active promotions
        $hasOther = FeaturedPromotion::where('property_id', $promotion->property_id)
            ->where('id', '!=', $promotion->id)
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->exists();

        if (!$hasOther && $promotion->property) {
            $promotion->property->update(['is_featured' => false]);
        }

        Cache::forget('featured_properties');
        Cache::forget("property_details_{$promotion->property_id}");

        return back()->with('success', 'Promotion has been expired.');
    }
}
