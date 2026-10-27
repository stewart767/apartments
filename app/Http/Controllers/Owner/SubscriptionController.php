<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
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
     * Display the owner's subscription and entitlement dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $org = $user->resolveOrganization();
        $subscription = $org->active_subscription;
        $invoices = $org->subscriptionInvoices()->with('plan')->latest()->paginate(10);
        $plans = Plan::active()->get();

        $propertiesUsed = $org->total_properties_count;
        $propertiesLimit = $subscription ? $subscription->plan->property_limit : 0;

        $unitsUsed = $org->total_units_count;
        $unitsLimit = $subscription ? $subscription->plan->unit_limit : 0;

        $staffUsed = $org->total_staff_count;
        $staffLimit = $subscription ? $subscription->plan->staff_limit : 0;

        return view('owner.subscription.index', compact(
            'org',
            'subscription',
            'invoices',
            'plans',
            'propertiesUsed',
            'propertiesLimit',
            'unitsUsed',
            'unitsLimit',
            'staffUsed',
            'staffLimit'
        ));
    }

    /**
     * Initiate a new plan subscription or plan switch.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'required|in:monthly,annual',
            'payment_method' => 'required|in:mpesa,tigopesa,airtelmoney,bank_transfer,cash',
            'payment_reference' => 'required|string|max:100',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
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

        return redirect()->route('owner.subscription')
            ->with('success', "Subscription order {$order['invoice']->invoice_number} placed successfully! It will be activated immediately upon admin payment verification.");
    }

    /**
     * Upload or submit proof for a pending subscription invoice.
     */
    public function submitPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:mpesa,tigopesa,airtelmoney,bank_transfer,cash',
            'payment_reference' => 'required|string|max:100',
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $user = auth()->user();
        $org = $user->resolveOrganization();
        $invoice = SubscriptionInvoice::where('organization_id', $org->id)->findOrFail($id);

        $path = $request->file('receipt')->store('subscription_receipts', 'public');

        $invoice->update([
            'payment_method' => $request->input('payment_method'),
            'payment_reference' => $request->input('payment_reference'),
            'receipt_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('owner.subscription')
            ->with('success', 'Payment receipt uploaded successfully. Our administrator will verify your transaction shortly.');
    }

    /**
     * Request subscription cancellation.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $user = auth()->user();
        $org = $user->resolveOrganization();
        $subscription = $org->active_subscription;

        if (!$subscription) {
            return back()->with('error', 'No active subscription found to cancel.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('cancellation_reason'),
        ]);

        return redirect()->route('owner.subscription')
            ->with('info', 'Your subscription cancellation has been recorded. You will retain access until the end of your current billing period.');
    }
}
