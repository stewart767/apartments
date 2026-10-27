<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\FeaturedPromotion;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class FeaturedPromotionController extends Controller
{
    /**
     * Store and submit a paid featured listing promotion.
     */
    public function store(Request $request, $propertyId)
    {
        $request->validate([
            'tier' => 'required|in:standard,extended,premium',
            'payment_reference' => 'required|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $user = auth()->user();
        $org = $user->resolveOrganization();
        $property = Property::where('owner_id', $user->id)->findOrFail($propertyId);

        $tier = $request->input('tier');
        $pricing = [
            'standard' => ['days' => 7, 'amount' => 5000],
            'extended' => ['days' => 14, 'amount' => 10000],
            'premium' => ['days' => 30, 'amount' => 15000],
        ];

        $config = $pricing[$tier];

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('promotion_receipts', 'public');
        }

        $promotion = FeaturedPromotion::create([
            'property_id' => $property->id,
            'organization_id' => $org->id,
            'tier' => $tier,
            'duration_days' => $config['days'],
            'amount' => $config['amount'],
            'currency' => 'TZS',
            'status' => 'pending_payment',
            'payment_reference' => $request->input('payment_reference'),
            'receipt_path' => $receiptPath,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'featured_promotion_ordered',
            'model_type' => FeaturedPromotion::class,
            'model_id' => $promotion->id,
            'description' => "Ordered {$tier} featured promotion for {$property->property_name} (TSh " . number_format($config['amount']) . ")",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Featured listing promotion submitted for {$property->property_name}! It will be activated immediately once payment reference {$promotion->payment_reference} is verified by administration.");
    }
}
