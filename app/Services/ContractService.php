<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Application;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Notifications\HostelSystemNotification;

class ContractService
{
    /**
     * Generate a new contract from an approved application.
     */
    public function generateContract(Application $application): Contract
    {
        return DB::transaction(function () use ($application) {
            // Check if contract already exists to prevent duplicate creation
            $existing = Contract::where('application_id', $application->id)->first();
            if ($existing) {
                return $existing;
            }

            $contractNumber = 'CON-' . date('Ymd') . '-' . Str::upper(Str::random(5));

            // Standard terms
            $rules = "1. Keep the room and common spaces clean.\n2. No loud noise after 10:00 PM.\n3. Do not share room keys with third parties.";
            $terms = "This agreement is made between the Tenant and the Property Owner. Rent is payable according to the billing frequency specified. Failure to pay within 5 days of the due date will incur overdue penalties. Refund of the deposit is subject to checked-out inspection clearance.";

            // Standard deposit: let's match the application price
            $deposit = $application->price; 

            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'application_id' => $application->id,
                'tenant_id' => $application->tenant_id,
                'property_id' => $application->property_id,
                'building_id' => $application->room->building_id ?? null,
                'floor_id' => $application->room->floor_id ?? null,
                'room_id' => $application->room_id,
                'bed_id' => $application->bed_id,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
                'rent_amount' => $application->price,
                'deposit_amount' => $deposit,
                'billing_frequency' => $application->billing_period,
                'rules' => $rules,
                'terms_and_conditions' => $terms,
                'status' => 'pending_acceptance',
            ]);

            // Auto-generate initial Rent & Deposit invoices!
            // This satisfies Phase 6 billing requirements immediately when contract is made.
            $this->generateInitialInvoices($contract);

            ActivityLog::create([
                'user_id' => $application->property->owner_id,
                'action' => 'contract_created',
                'model_type' => Contract::class,
                'model_id' => $contract->id,
                'description' => "Contract {$contractNumber} generated for Application {$application->application_number}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Tenant
            $tenant = $contract->tenant;
            if ($tenant) {
                $tenant->notify(new HostelSystemNotification(
                    'Lease Agreement Ready',
                    "Your lease agreement {$contractNumber} has been generated and is ready for your signature.",
                    route('tenant.contracts.show', $contract->id)
                ));
            }

            // Clear stats caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$contract->property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $contract->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }
            Cache::forget("property_details_{$contract->property_id}");
            Cache::forget('featured_properties');

            return $contract;
        });
    }

    /**
     * Tenant accepts the contract terms.
     */
    public function tenantAcceptContract(Contract $contract): bool
    {
        return DB::transaction(function () use ($contract) {
            $updated = $contract->update([
                'tenant_accepted' => true,
                'tenant_accepted_at' => now(),
                'status' => 'accepted' // Moves to accepted pending payment
            ]);

            ActivityLog::create([
                'user_id' => $contract->tenant_id,
                'action' => 'contract_accepted',
                'model_type' => Contract::class,
                'model_id' => $contract->id,
                'description' => "Tenant signed and accepted Contract {$contract->contract_number}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notify Owner
            $owner = $contract->property->owner;
            if ($owner) {
                $owner->notify(new HostelSystemNotification(
                    'Lease Agreement Signed',
                    "Tenant {$contract->tenant->name} signed and accepted lease agreement {$contract->contract_number}.",
                    route('owner.contracts.show', $contract->id)
                ));
            }

            // Clear stats caches
            Cache::forget('superadmin_dashboard_stats');
            Cache::forget("owner_dashboard_stats_{$contract->property->owner_id}");
            $managerIds = DB::table('property_user')
                ->where('property_id', $contract->property_id)
                ->pluck('user_id');
            foreach ($managerIds as $mid) {
                Cache::forget("manager_dashboard_stats_{$mid}");
            }
            Cache::forget("property_details_{$contract->property_id}");
            Cache::forget('featured_properties');

            return $updated;
        });
    }

    /**
     * Auto-generate deposit and initial rent invoices when a contract is created.
     */
    protected function generateInitialInvoices(Contract $contract): void
    {
        // 1. Generate Deposit Payment Record (Due immediately)
        if ($contract->deposit_amount > 0) {
            Payment::create([
                'payment_reference' => 'PAY-DEP-' . date('Ymd') . '-' . Str::upper(Str::random(4)),
                'tenant_id' => $contract->tenant_id,
                'property_id' => $contract->property_id,
                'contract_id' => $contract->id,
                'payment_type' => 'deposit',
                'amount' => $contract->deposit_amount,
                'due_date' => now(),
                'status' => 'pending',
                'notes' => 'Contract refundable security deposit.',
            ]);
        }

        // 2. Generate First Month Rent Payment Record (Due on or before start_date)
        Payment::create([
            'payment_reference' => 'PAY-RNT-' . date('Ymd') . '-' . Str::upper(Str::random(4)),
            'tenant_id' => $contract->tenant_id,
            'property_id' => $contract->property_id,
            'contract_id' => $contract->id,
            'payment_type' => 'rent',
            'amount' => $contract->rent_amount,
            'due_date' => $contract->start_date,
            'status' => 'pending',
            'notes' => 'Initial rent payment invoice.',
        ]);
    }
}
