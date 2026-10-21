<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Tenant views their invoices & payment history.
     */
    public function tenantPayments()
    {
        $tenantId = auth()->id();
        $payments = Payment::with(['property', 'contract'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);

        return view('tenant.payments.index', compact('payments'));
    }

    /**
     * Property Owner views payments received & pending confirmation.
     */
    public function ownerPayments()
    {
        $ownerId = auth()->id();
        $payments = Payment::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property', 'tenant', 'contract'])
        ->latest()
        ->paginate(10);

        return view('owner.payments.index', compact('payments'));
    }

    /**
     * Submit proof of payment (Tenant operation).
     */
    public function submitPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'required|string|max:100|unique:payments,payment_reference,' . $id,
            'receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $payment = Payment::where('tenant_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($id);

        if ($request->hasFile('receipt')) {
            $this->paymentService->submitPaymentProof(
                $payment,
                $request->only(['payment_method', 'payment_reference']),
                $request->file('receipt')
            );

            return redirect()->route('tenant.payments')
                ->with('success', 'Your payment proof has been submitted successfully and is awaiting owner verification.');
        }

        return back()->with('error', 'Failed to upload receipt proof.');
    }

    /**
     * Verify and approve payment (Owner/Manager operation).
     */
    public function verifyPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = Payment::with('property')->findOrFail($id);
        $this->authorizePropertyManagement($payment);

        $action = $request->input('action');
        $this->paymentService->verifyPayment($payment, $action, $request->input('notes'), auth()->user());

        if ($action === 'approve') {
            return back()->with('success', 'Rent payment reference verified and marked as PAID.');
        } else {
            return back()->with('error', 'Rent payment reference was rejected.');
        }
    }

    /**
     * Helper to verify role access permissions.
     */
    protected function authorizePropertyManagement(Payment $payment)
    {
        $user = auth()->user();
        if ($user->isSuperadmin()) {
            return true;
        }
        if ($payment->property->owner_id === $user->id) {
            return true;
        }
        if ($user->isManager() || $user->isReceptionist()) {
            $hasAssignment = DB::table('property_user')
                ->where('property_id', $payment->property_id)
                ->where('user_id', $user->id)
                ->exists();
            if ($hasAssignment) {
                return true;
            }
        }

        abort(403, 'Unauthorized payment management operation.');
    }
}
