<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\CheckIn;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Asset;
use App\Models\AssetHandoverRecord;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\HostelSystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class CheckinService
{
    /**
     * Process tenant check-in.
     */
    public function processCheckin(Contract $contract, array $data, ?User $staff = null): CheckIn
    {
        return DB::transaction(function () use ($contract, $data, $staff) {
            $staffId = $staff ? $staff->id : (auth()->id() ?? $contract->property->owner_id);

            $inventory = [
                'keys_given' => (int) ($data['keys_count'] ?? 1),
                'room_condition' => $data['room_condition_notes'] ?? null,
                'property_assets' => $data['property_assets'] ?? [],
                'tenant_assets' => $data['tenant_assets'] ?? [],
            ];

            // 1. Create CheckIn record
            $checkIn = CheckIn::create([
                'tenant_id' => $contract->tenant_id,
                'contract_id' => $contract->id,
                'check_in_date' => $data['check_in_date'] ?? now()->toDateString(),
                'deposit_received' => $data['deposit_received'] ?? $contract->deposit_amount,
                'room_condition_notes' => $data['room_condition_notes'] ?? null,
                'inventory_json' => $inventory,
                'staff_id' => $staffId,
                'notes' => $data['notes'] ?? 'Checked in successfully with verified assets baseline.',
            ]);

            // 2. Activate Contract
            $contract->update(['status' => 'active']);

            // 3. Update Bed status to occupied
            if ($contract->bed_id) {
                Bed::where('id', $contract->bed_id)->update([
                    'status' => 'occupied',
                    'current_tenant_id' => $contract->tenant_id,
                ]);
            }

            // 4. Update Room occupancy
            if ($contract->room_id) {
                $room = Room::find($contract->room_id);
                if ($room) {
                    $roomModel = $room->rental_model ?? $contract->property->rent_structure;
                    if ($roomModel === 'per_room' || $roomModel === 'per_unit' || $roomModel === 'entire_property') {
                        $room->update(['occupancy' => $room->capacity]);
                    } else {
                        $room->update(['occupancy' => min($room->capacity, $room->occupancy + 1)]);
                    }
                }
            }

            // 5. Create auto handover records for property assets
            $propertyAssets = $data['property_assets'] ?? [];
            foreach ($propertyAssets as $assetId => $condition) {
                $asset = Asset::find($assetId);
                if ($asset) {
                    AssetHandoverRecord::create([
                        'tenant_id' => $contract->tenant_id,
                        'property_id' => $contract->property_id,
                        'room_id' => $contract->room_id,
                        'bed_id' => $contract->bed_id,
                        'asset_id' => $asset->id,
                        'condition_at_handover' => in_array($condition, ['new', 'excellent', 'good', 'fair', 'damaged']) ? $condition : 'good',
                        'handover_date' => $data['check_in_date'] ?? now()->toDateString(),
                        'staff_id' => $staffId,
                        'tenant_confirmed' => true,
                        'tenant_confirmed_at' => now(),
                        'notes' => 'Auto-assigned on tenant check-in baseline.',
                    ]);

                    $asset->update([
                        'status' => 'assigned',
                        'condition' => in_array($condition, ['new', 'excellent', 'good', 'fair', 'damaged', 'needs_repair', 'missing', 'disposed']) ? $condition : 'good'
                    ]);
                }
            }

            // 6. Activity log
            ActivityLog::create([
                'user_id' => $staffId,
                'action' => 'tenant_checked_in',
                'model_type' => Contract::class,
                'model_id' => $contract->id,
                'description' => "Tenant {$contract->tenant->name} checked in under contract {$contract->contract_number}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 7. Notify Tenant
            $tenant = $contract->tenant;
            if ($tenant) {
                $roomNumber = $contract->room ? $contract->room->room_number : 'Accommodation';
                $tenant->notify(new HostelSystemNotification(
                    'Checked In Successfully',
                    "You have been checked in to {$roomNumber} under contract {$contract->contract_number}.",
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

            return $checkIn->fresh();
        });
    }
}
