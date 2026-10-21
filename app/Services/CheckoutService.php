<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\CheckOut;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Asset;
use App\Models\TenantAsset;
use App\Models\AssetHandoverRecord;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\HostelSystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class CheckoutService
{
    /**
     * Process tenant check-out with damage deduction & deposit calculation.
     */
    public function processCheckout(Contract $contract, array $data, ?User $staff = null): CheckOut
    {
        return DB::transaction(function () use ($contract, $data, $staff) {
            $staffId = $staff ? $staff->id : (auth()->id() ?? $contract->property->owner_id);

            // 1. Process property assets checkout inspection
            $propAssetsLog = [];
            $checkoutPropAssets = $data['checkout_property_assets'] ?? [];
            foreach ($checkoutPropAssets as $assetId => $condition) {
                $asset = Asset::find($assetId);
                if ($asset) {
                    $oldCondition = $asset->condition;
                    $propAssetsLog[] = "{$asset->asset_name} (Code: {$asset->asset_code}): Baseline '{$oldCondition}' -> Checkout '{$condition}'";

                    $asset->update([
                        'condition' => in_array($condition, ['new', 'excellent', 'good', 'fair', 'damaged', 'needs_repair', 'missing', 'disposed']) ? $condition : 'good',
                        'status' => in_array($condition, ['damaged', 'needs_repair']) ? 'maintenance' : 'available',
                    ]);

                    AssetHandoverRecord::where('asset_id', $asset->id)
                        ->where('tenant_id', $contract->tenant_id)
                        ->update(['notes' => ($asset->notes ? $asset->notes . ' | ' : '') . "Handed back on checkout. Condition: {$condition}."]);
                }
            }

            // 2. Process tenant personal assets
            $tenantAssetsLog = [];
            $checkoutTenantAssets = $data['checkout_tenant_assets'] ?? [];
            foreach ($checkoutTenantAssets as $assetId => $condition) {
                $tAsset = TenantAsset::find($assetId);
                if ($tAsset) {
                    $tenantAssetsLog[] = "{$tAsset->asset_name}: Condition '{$condition}'";
                    $tAsset->update(['condition' => $condition]);
                }
            }

            $damageCharges = (float) ($data['damage_charges'] ?? 0.00);
            $depositReceived = (float) ($contract->checkIn->deposit_received ?? $contract->deposit_amount);
            $calculatedRefund = max(0.00, $depositReceived - $damageCharges);
            $depositRefunded = isset($data['deposit_refunded']) ? (float) $data['deposit_refunded'] : $calculatedRefund;

            $notes = ($data['notes'] ?? '') . "\n\nProperty Assets Checklist:\n" . implode("\n", $propAssetsLog) . "\n\nTenant Assets Checklist:\n" . implode("\n", $tenantAssetsLog);

            // 3. Create CheckOut record
            $checkOut = CheckOut::create([
                'tenant_id' => $contract->tenant_id,
                'contract_id' => $contract->id,
                'check_out_date' => $data['check_out_date'] ?? now()->toDateString(),
                'room_condition_notes' => $data['room_condition_notes'] ?? null,
                'damage_charges' => $damageCharges,
                'deposit_refunded' => $depositRefunded,
                'outstanding_balance_settled' => (bool) ($data['outstanding_settled'] ?? true),
                'staff_id' => $staffId,
                'notes' => trim($notes),
            ]);

            // 4. Terminate Contract
            $contract->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'termination_reason' => $data['termination_reason'] ?? 'Lease checkout completed with inventory comparison.'
            ]);

            // 5. Free Bed
            if ($contract->bed_id) {
                Bed::where('id', $contract->bed_id)->update([
                    'status' => 'available',
                    'current_tenant_id' => null,
                ]);
            }

            // 6. Adjust Room occupancy
            if ($contract->room_id) {
                $room = Room::find($contract->room_id);
                if ($room) {
                    $roomModel = $room->rental_model ?? $contract->property->rent_structure;
                    if ($roomModel === 'per_room' || $roomModel === 'per_unit' || $roomModel === 'entire_property') {
                        $room->update(['occupancy' => 0]);
                    } else {
                        $room->update(['occupancy' => max(0, $room->occupancy - 1)]);
                    }
                }
            }

            // 7. Activity Log
            ActivityLog::create([
                'user_id' => $staffId,
                'action' => 'tenant_checked_out',
                'model_type' => Contract::class,
                'model_id' => $contract->id,
                'description' => "Tenant {$contract->tenant->name} checked out. Damage: {$damageCharges}, Refund: {$depositRefunded}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 8. Notify Tenant
            $tenant = $contract->tenant;
            if ($tenant) {
                $tenant->notify(new HostelSystemNotification(
                    'Checked Out Successfully',
                    "Your check-out for contract {$contract->contract_number} has been completed. Deposit refunded: {$depositRefunded}.",
                    route('tenant.contracts.show', $contract->id)
                ));
            }

            // Invalidate caches
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

            return $checkOut->fresh();
        });
    }
}
