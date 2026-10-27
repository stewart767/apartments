<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\SubscriptionEntitlementService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $entitlementService;

    public function __construct(SubscriptionEntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Get owner organization subscription and usage quota.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $org = $user->resolveOrganization();
        $subscription = $org->active_subscription;

        $usage = [
            'units' => [
                'used' => $org->total_units_count,
                'limit' => $subscription ? $subscription->plan->unit_limit : 0,
            ],
            'properties' => [
                'used' => $org->total_properties_count,
                'limit' => $subscription ? $subscription->plan->property_limit : 0,
            ],
            'staff' => [
                'used' => $org->total_staff_count,
                'limit' => $subscription ? $subscription->plan->staff_limit : 0,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Subscription details retrieved.',
            'data' => [
                'organization' => [
                    'id' => $org->id,
                    'name' => $org->name,
                ],
                'subscription' => $subscription ? [
                    'id' => $subscription->id,
                    'plan' => $subscription->plan->name,
                    'status' => $subscription->status,
                    'billing_interval' => $subscription->billing_interval,
                    'price' => $subscription->price,
                    'currency' => $subscription->currency,
                    'current_period_ends_at' => $subscription->current_period_ends_at?->toIso8601String(),
                    'days_remaining' => $subscription->days_remaining,
                ] : null,
                'usage' => $usage,
                'recent_invoices' => $org->subscriptionInvoices()->latest()->take(5)->get(),
            ],
        ]);
    }

    /**
     * Place subscription order via API.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'required|in:monthly,annual',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $org = $user->resolveOrganization();
        $plan = Plan::findOrFail($request->input('plan_id'));

        $order = $this->entitlementService->createSubscriptionOrder(
            $org,
            $plan,
            $request->input('billing_interval'),
            $request->input('payment_method'),
            $request->input('payment_reference'),
            $request->file('receipt'),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription order placed successfully.',
            'data' => [
                'subscription_id' => $order['subscription']->id,
                'invoice_number' => $order['invoice']->invoice_number,
                'amount' => $order['invoice']->amount,
                'status' => $order['invoice']->status,
            ],
        ], 201);
    }
}
