<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Services\SubscriptionEntitlementService;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    protected $entitlementService;

    public function __construct(SubscriptionEntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Display SaaS subscriptions and invoice verification management.
     */
    public function index(Request $request)
    {
        $totalSaasRevenue = SubscriptionInvoice::where('status', 'paid')->sum('amount');
        $activeSubscriptionsCount = Subscription::where('status', 'active')->count();
        $trialingCount = Subscription::where('status', 'trialing')->count();
        $pendingInvoicesCount = SubscriptionInvoice::where('status', 'pending')->count();

        $query = SubscriptionInvoice::with(['organization.owner', 'plan', 'verifier'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->paginate(15);
        $organizations = Organization::with(['owner', 'currentSubscription.plan'])->latest()->paginate(10);
        $plans = Plan::all();

        return view('admin.subscriptions.index', compact(
            'totalSaasRevenue',
            'activeSubscriptionsCount',
            'trialingCount',
            'pendingInvoicesCount',
            'invoices',
            'organizations',
            'plans'
        ));
    }

    /**
     * Verify or reject a subscription invoice payment proof.
     */
    public function verifyInvoice(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'verification_notes' => 'nullable|string|max:500',
        ]);

        $invoice = SubscriptionInvoice::with('organization')->findOrFail($id);

        $this->entitlementService->verifySubscriptionPayment(
            $invoice,
            $request->input('action'),
            $request->input('verification_notes'),
            auth()->user()
        );

        $msg = $request->input('action') === 'approve'
            ? "Subscription invoice {$invoice->invoice_number} approved and plan activated!"
            : "Subscription invoice {$invoice->invoice_number} was rejected.";

        return back()->with('success', $msg);
    }

    /**
     * Grant promotional trial access to an organization.
     */
    public function grantPromo(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'plan_id' => 'required|exists:plans,id',
            'days' => 'required|integer|min:1|max:90',
            'reason' => 'required|string|max:255',
        ]);

        $org = Organization::findOrFail($request->input('organization_id'));
        $plan = Plan::findOrFail($request->input('plan_id'));

        $this->entitlementService->grantPromotionalAccess(
            $org,
            $plan,
            (int) $request->input('days'),
            $request->input('reason')
        );

        return back()->with('success', "Granted {$request->input('days')} days promotional trial for {$org->name} on {$plan->name} plan.");
    }
}
