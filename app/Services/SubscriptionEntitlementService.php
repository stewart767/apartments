<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SubscriptionEntitlementService
{
    /**
     * Check if organization can create a new property.
     */
    public function canCreateProperty(Organization $org): array
    {
        $subscription = $org->active_subscription;

        if (!$subscription) {
            if (Plan::count() === 0) {
                return ['allowed' => true];
            }

            return [
                'allowed' => false,
                'code' => 'no_subscription',
                'message' => 'An active subscription is required to add and manage properties. Please select a plan to continue.',
                'upgrade_required' => true,
            ];
        }

        $plan = $subscription->plan;
        $currentProperties = $org->total_properties_count;

        if ($currentProperties >= $plan->property_limit) {
            return [
                'allowed' => false,
                'code' => 'property_limit_reached',
                'message' => "Your {$plan->name} plan limit of {$plan->property_limit} property(ies) has been reached ({$currentProperties} currently listed). Please upgrade your plan to list more properties.",
                'upgrade_required' => true,
                'current' => $currentProperties,
                'limit' => $plan->property_limit,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check if organization can add rooms/beds/units.
     */
    public function canAddUnits(Organization $org, int $unitsToAdd = 1): array
    {
        $subscription = $org->active_subscription;

        if (!$subscription) {
            if (Plan::count() === 0) {
                return ['allowed' => true];
            }

            return [
                'allowed' => false,
                'code' => 'no_subscription',
                'message' => 'An active subscription is required to add rooms or rental units. Please subscribe to a plan.',
                'upgrade_required' => true,
            ];
        }

        $plan = $subscription->plan;
        $currentUnits = $org->total_units_count;

        if (($currentUnits + $unitsToAdd) > $plan->unit_limit) {
            return [
                'allowed' => false,
                'code' => 'unit_limit_reached',
                'message' => "Adding {$unitsToAdd} unit(s) exceeds your {$plan->name} plan limit of {$plan->unit_limit} units (currently managing {$currentUnits} units). Please upgrade your subscription to expand capacity.",
                'upgrade_required' => true,
                'current' => $currentUnits,
                'limit' => $plan->unit_limit,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check if organization can add staff members.
     */
    public function canAddStaff(Organization $org): array
    {
        $subscription = $org->active_subscription;

        if (!$subscription) {
            if (Plan::count() === 0) {
                return ['allowed' => true];
            }

            return [
                'allowed' => false,
                'code' => 'no_subscription',
                'message' => 'An active subscription is required to add staff members.',
                'upgrade_required' => true,
            ];
        }

        $plan = $subscription->plan;
        $currentStaff = $org->total_staff_count;

        if ($currentStaff >= $plan->staff_limit) {
            return [
                'allowed' => false,
                'code' => 'staff_limit_reached',
                'message' => "Your {$plan->name} plan limit of {$plan->staff_limit} staff account(s) has been reached. Please upgrade to add more managers or receptionists.",
                'upgrade_required' => true,
                'current' => $currentStaff,
                'limit' => $plan->staff_limit,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check if organization plan has a specific feature.
     */
    public function hasFeature(Organization $org, string $feature): bool
    {
        $subscription = $org->active_subscription;
        if (!$subscription || !$subscription->plan) {
            return false;
        }

        return $subscription->plan->hasFeature($feature);
    }

    /**
     * Subscribe or upgrade an organization to a plan.
     */
    public function createSubscriptionOrder(
        Organization $org,
        Plan $plan,
        string $interval = 'monthly',
        ?string $paymentMethod = 'bank_transfer',
        ?string $paymentReference = null,
        ?UploadedFile $receipt = null,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($org, $plan, $interval, $paymentMethod, $paymentReference, $receipt, $notes) {
            $isAnnual = $interval === 'annual';
            $price = $isAnnual ? (float) $plan->price_annual : (float) $plan->price_monthly;

            // Generate unique invoice reference
            $invoiceNumber = 'SUB-INV-' . date('Ymd') . '-' . Str::upper(Str::random(4));

            $receiptPath = null;
            if ($receipt) {
                $receiptPath = $receipt->store('subscription_receipts', 'public');
            }

            // Create or update pending subscription
            $subscription = Subscription::create([
                'organization_id' => $org->id,
                'plan_id' => $plan->id,
                'billing_interval' => $interval,
                'status' => 'past_due', // Marked active upon verification
                'price' => $price,
                'currency' => 'TZS',
                'starts_at' => now(),
                'current_period_starts_at' => now(),
                'current_period_ends_at' => $isAnnual ? now()->addYear() : now()->addMonth(),
                'notes' => $notes ?? "Order for {$plan->name} ({$interval})",
            ]);

            // Create standalone SubscriptionInvoice (separate from tenant rent)
            $invoice = SubscriptionInvoice::create([
                'invoice_number' => $invoiceNumber,
                'organization_id' => $org->id,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'amount' => $price,
                'currency' => 'TZS',
                'billing_interval' => $interval,
                'status' => 'pending',
                'due_date' => now()->toDateString(),
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'receipt_path' => $receiptPath,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id() ?? $org->owner_user_id,
                'action' => 'subscription_order_created',
                'model_type' => Subscription::class,
                'model_id' => $subscription->id,
                'description' => "Placed subscription order {$invoiceNumber} for {$plan->name} ({$interval}) - TSh " . number_format($price),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'subscription' => $subscription,
                'invoice' => $invoice,
            ];
        });
    }

    /**
     * Admin verifies subscription payment proof.
     */
    public function verifySubscriptionPayment(
        SubscriptionInvoice $invoice,
        string $action,
        ?string $notes = null,
        ?User $verifier = null
    ): SubscriptionInvoice {
        return DB::transaction(function () use ($invoice, $action, $notes, $verifier) {
            $verifierId = $verifier ? $verifier->id : auth()->id();

            if ($action === 'approve') {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'verified_by' => $verifierId,
                    'verified_at' => now(),
                    'verification_notes' => $notes ?? 'Payment approved and verified.',
                ]);

                // Activate subscription
                if ($invoice->subscription) {
                    $isAnnual = $invoice->billing_interval === 'annual';
                    $startDate = now();

                    // If existing subscription was active and unexpired, extend it!
                    if ($invoice->subscription->current_period_ends_at && $invoice->subscription->current_period_ends_at->isFuture()) {
                        $startDate = $invoice->subscription->current_period_ends_at;
                    }

                    $endDate = $isAnnual ? $startDate->copy()->addYear() : $startDate->copy()->addMonth();

                    $invoice->subscription->update([
                        'status' => 'active',
                        'current_period_starts_at' => now(),
                        'current_period_ends_at' => $endDate,
                        'notes' => trim(($invoice->subscription->notes ?? '') . " | Verified by Admin #{$verifierId}"),
                    ]);
                }

                ActivityLog::create([
                    'user_id' => $verifierId,
                    'action' => 'subscription_payment_verified',
                    'model_type' => SubscriptionInvoice::class,
                    'model_id' => $invoice->id,
                    'description' => "Verified subscription invoice {$invoice->invoice_number} for organization {$invoice->organization->name}.",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } else {
                $invoice->update([
                    'status' => 'failed',
                    'verified_by' => $verifierId,
                    'verified_at' => now(),
                    'verification_notes' => $notes ?? 'Payment receipt rejected.',
                ]);

                if ($invoice->subscription) {
                    $invoice->subscription->update(['status' => 'expired']);
                }

                ActivityLog::create([
                    'user_id' => $verifierId,
                    'action' => 'subscription_payment_rejected',
                    'model_type' => SubscriptionInvoice::class,
                    'model_id' => $invoice->id,
                    'description' => "Rejected subscription payment {$invoice->invoice_number}. Reason: {$notes}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Grant promotional or trial access to an organization.
     */
    public function grantPromotionalAccess(
        Organization $org,
        Plan $plan,
        int $days = 14,
        ?string $reason = 'Promotional Trial'
    ): Subscription {
        return DB::transaction(function () use ($org, $plan, $days, $reason) {
            $subscription = Subscription::create([
                'organization_id' => $org->id,
                'plan_id' => $plan->id,
                'billing_interval' => 'monthly',
                'status' => 'trialing',
                'price' => 0,
                'currency' => 'TZS',
                'starts_at' => now(),
                'current_period_starts_at' => now(),
                'current_period_ends_at' => now()->addDays($days),
                'trial_ends_at' => now()->addDays($days),
                'notes' => "Granted {$days} days promotional trial. Reason: {$reason}",
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'promotional_subscription_granted',
                'model_type' => Subscription::class,
                'model_id' => $subscription->id,
                'description' => "Granted {$days}-day promotional trial for {$org->name} on {$plan->name} plan.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $subscription;
        });
    }
}
