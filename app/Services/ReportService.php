<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Bed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    /**
     * Get owner dashboard and reporting statistics.
     */
    public function getOwnerStats(int $ownerId): array
    {
        return Cache::remember("owner_dashboard_stats_{$ownerId}", 300, function () use ($ownerId) {
            $propertyIds = Property::where('owner_id', $ownerId)->pluck('id')->toArray();

            if (empty($propertyIds)) {
                return [
                    'total_properties' => 0,
                    'occupancy_rate' => 0,
                    'available_rooms' => 0,
                    'available_beds' => 0,
                    'total_tenants' => 0,
                    'pending_applications' => 0,
                    'total_revenue' => 0.00,
                    'outstanding_payments' => 0.00,
                    'expiring_contracts' => 0,
                    'maintenance_requests' => 0,
                ];
            }

            $revenue = (float) Payment::whereIn('property_id', $propertyIds)
                ->where('status', 'paid')
                ->sum('amount');

            $outstanding = (float) Payment::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount');

            $totalBeds = Bed::whereHas('room', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->count();
            
            $availableBeds = Bed::whereHas('room', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->where('status', 'available')->count();

            $totalRooms = Room::whereIn('property_id', $propertyIds)->count();
            $occupiedRooms = Contract::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['accepted', 'active'])
                ->count();

            $occupancyRate = 0;
            if ($totalBeds > 0) {
                $occupancyRate = round((($totalBeds - $availableBeds) / $totalBeds) * 100, 1);
            } elseif ($totalRooms > 0) {
                $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 1);
            }

            return [
                'total_properties' => count($propertyIds),
                'occupancy_rate' => $occupancyRate,
                'available_rooms' => max(0, Room::whereIn('property_id', $propertyIds)->where('status', 'active')->count() - $occupiedRooms),
                'available_beds' => $availableBeds,
                'total_tenants' => Contract::whereIn('property_id', $propertyIds)->where('status', 'active')->count(),
                'pending_applications' => Application::whereIn('property_id', $propertyIds)->where('status', 'pending')->count(),
                'total_revenue' => $revenue,
                'outstanding_payments' => $outstanding,
                'expiring_contracts' => Contract::whereIn('property_id', $propertyIds)
                    ->where('status', 'active')
                    ->where('end_date', '<=', now()->addDays(30))
                    ->count(),
                'maintenance_requests' => MaintenanceRequest::whereIn('property_id', $propertyIds)
                    ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
                    ->count(),
            ];
        });
    }

    /**
     * Get manager dashboard statistics.
     */
    public function getManagerStats(int $managerId): array
    {
        return Cache::remember("manager_dashboard_stats_{$managerId}", 300, function () use ($managerId) {
            $assignedPropertyIds = DB::table('property_user')
                ->where('user_id', $managerId)
                ->pluck('property_id')
                ->toArray();

            if (empty($assignedPropertyIds)) {
                return [
                    'assigned_properties' => 0,
                    'occupancy_rate' => 0,
                    'available_rooms' => 0,
                    'available_beds' => 0,
                    'total_tenants' => 0,
                    'pending_applications' => 0,
                    'maintenance_requests' => 0,
                ];
            }

            $totalBeds = Bed::whereHas('room', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->count();
            
            $availableBeds = Bed::whereHas('room', function($q) use ($assignedPropertyIds) {
                $q->whereIn('property_id', $assignedPropertyIds);
            })->where('status', 'available')->count();

            $totalRooms = Room::whereIn('property_id', $assignedPropertyIds)->count();
            $occupiedRooms = Contract::whereIn('property_id', $assignedPropertyIds)
                ->whereIn('status', ['accepted', 'active'])
                ->count();

            $occupancyRate = 0;
            if ($totalBeds > 0) {
                $occupancyRate = round((($totalBeds - $availableBeds) / $totalBeds) * 100, 1);
            } elseif ($totalRooms > 0) {
                $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 1);
            }

            return [
                'assigned_properties' => count($assignedPropertyIds),
                'occupancy_rate' => $occupancyRate,
                'available_rooms' => max(0, Room::whereIn('property_id', $assignedPropertyIds)->where('status', 'active')->count() - $occupiedRooms),
                'available_beds' => $availableBeds,
                'total_tenants' => Contract::whereIn('property_id', $assignedPropertyIds)->where('status', 'active')->count(),
                'pending_applications' => Application::whereIn('property_id', $assignedPropertyIds)->where('status', 'pending')->count(),
                'maintenance_requests' => MaintenanceRequest::whereIn('property_id', $assignedPropertyIds)
                    ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
                    ->count(),
            ];
        });
    }

    /**
     * Get tenant dashboard statistics.
     */
    public function getTenantDashboardData(int $tenantId): array
    {
        $activeContract = Contract::with(['property.primaryImage', 'room', 'bed'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['accepted', 'active'])
            ->latest()
            ->first();

        $nextPayment = Payment::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();

        $pendingApplicationsCount = Application::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $openMaintenanceCount = MaintenanceRequest::where('tenant_id', $tenantId)
            ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
            ->count();

        return [
            'active_contract' => $activeContract,
            'next_payment' => $nextPayment,
            'pending_applications_count' => $pendingApplicationsCount,
            'open_maintenance_count' => $openMaintenanceCount,
        ];
    }
}
