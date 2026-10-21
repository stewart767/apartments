<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\HostelSystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Exception;

class PaymentService
{
    /**
     * Submit payment proof receipt for a pending payment/invoice.
     */
    public function submitPaymentProof(Payment $payment, array $data, UploadedFile $receiptFile): Payment
    {
        return DB::transaction(function () use ($payment, $data, $receiptFile) {
            $path = $receiptFile->store('receipts', 'public');

            $payment->update([
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'],
                'receipt_path' => $path,
                'paid_date' => now(),
                'status' => 'partially_paid', // Used as "Awaiting verification / Submitted"
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            // If linked to an invoice, sync status
            if ($payment->invoice_id) {
                Invoice::where('id', $payment->invoice_id)->update(['status' => 'partially_paid']);
            }

            ActivityLog::create([
                'user_id' => $payment->tenant_id,
                'action' => 'payment_submitted',
                'model_type' => Payment::class,
                'model_id' => $payment->id,
                'description' => "Tenant submitted proof for payment reference {$payment->payment_reference}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Property Owner
            $owner = $payment->property->owner ?? null;
            if ($owner) {
                $owner->notify(new HostelSystemNotification(
                    'Rent Payment Receipt Submitted',
                    "Tenant {$payment->tenant->name} has uploaded a payment receipt for reference {$payment->payment_reference}.",
                    route('owner.payments')
                ));
            }

            return $payment->fresh();
        });
    }

    /**
     * Verify payment proof (Approve or Reject).
     */
    public function verifyPayment(Payment $payment, string $action, ?string $notes = null, ?User $verifier = null): Payment
    {
        return DB::transaction(function () use ($payment, $action, $notes, $verifier) {
            $verifierId = $verifier ? $verifier->id : (auth()->id() ?? $payment->property->owner_id);

            if ($action === 'approve') {
                $payment->update([
                    'status' => 'paid',
                    'notes' => $notes ?? $payment->notes,
                ]);

                // Update linked invoice to paid
                if ($payment->invoice_id) {
                    Invoice::where('id', $payment->invoice_id)->update(['status' => 'paid']);
                }

                ActivityLog::create([
                    'user_id' => $verifierId,
                    'action' => 'payment_verified',
                    'model_type' => Payment::class,
                    'model_id' => $payment->id,
                    'description' => "Verified and approved payment {$payment->payment_reference}.",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Notify Tenant
                $tenant = $payment->tenant;
                if ($tenant) {
                    $tenant->notify(new HostelSystemNotification(
                        'Rent Payment Verified',
                        "Your payment for reference {$payment->payment_reference} has been verified and marked as PAID.",
                        route('tenant.payments')
                    ));
                }
            } else {
                $payment->update([
                    'status' => 'failed',
                    'notes' => $notes ?? $payment->notes,
                ]);

                if ($payment->invoice_id) {
                    Invoice::where('id', $payment->invoice_id)->update(['status' => 'unpaid']);
                }

                ActivityLog::create([
                    'user_id' => $verifierId,
                    'action' => 'payment_rejected',
                    'model_type' => Payment::class,
                    'model_id' => $payment->id,
                    'description' => "Rejected payment {$payment->payment_reference}. Notes: {$notes}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Notify Tenant
                $tenant = $payment->tenant;
                if ($tenant) {
                    $tenant->notify(new HostelSystemNotification(
                        'Rent Payment Rejected',
                        "Your payment for reference {$payment->payment_reference} was rejected. Note: {$notes}",
                        route('tenant.payments')
                    ));
                }
            }

            // Invalidate caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$payment->property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $payment->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }

            return $payment->fresh();
        });
    }

    /**
     * Process direct gateway webhook reconciliation.
     */
    public function handleGatewayWebhook(string $gateway, array $payload, string $signature): array
    {
        // Future direct payment gateway webhook verification logic
        return [
            'status' => 'received',
            'gateway' => $gateway,
        ];
    }
}
