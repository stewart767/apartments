<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    /**
     * Submit a review for a specific tenancy lease contract.
     */
    public function store(Request $request, $contractId)
    {
        $request->validate([
            'cleanliness' => 'required|integer|min:1|max:5',
            'security' => 'required|integer|min:1|max:5',
            'water' => 'required|integer|min:1|max:5',
            'electricity' => 'required|integer|min:1|max:5',
            'management' => 'required|integer|min:1|max:5',
            'location' => 'required|integer|min:1|max:5',
            'value_for_money' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Ensure the tenant owns the contract and is a verified past/current active tenant
        $contract = Contract::where('tenant_id', auth()->id())
            ->whereIn('status', ['active', 'expired', 'terminated'])
            ->findOrFail($contractId);

        // Check if a review was already submitted for this contract
        $existing = Review::where('contract_id', $contract->id)->exists();
        if ($existing) {
            return back()->with('error', 'You have already submitted a review for this tenancy lease.');
        }

        // Create review
        Review::create([
            'tenant_id' => auth()->id(),
            'property_id' => $contract->property_id,
            'contract_id' => $contract->id,
            'cleanliness' => $request->input('cleanliness'),
            'security' => $request->input('security'),
            'water' => $request->input('water'),
            'electricity' => $request->input('electricity'),
            'management' => $request->input('management'),
            'location' => $request->input('location'),
            'value_for_money' => $request->input('value_for_money'),
            'comment' => $request->input('comment'),
            'is_approved' => true,
        ]);

        // Invalidate property details cache to update rating calculations
        Cache::forget("property_details_{$contract->property_id}");

        return back()->with('success', 'Thank you! Your review has been submitted and published successfully.');
    }
}
