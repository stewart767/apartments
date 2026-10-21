<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return $this->superadminDashboard();
        }

        if ($user->isOwner()) {
            return $this->ownerDashboard();
        }

        if ($user->isManager() || $user->isReceptionist()) {
            return $this->managerDashboard();
        }

        if ($user->isTenant()) {
            return $this->tenantDashboard();
        }

        abort(403, 'Unauthorized role.');
    }

    /**
     * Super Admin Dashboard. Statistics are cached for 10 minutes.
     */
    protected function superadminDashboard()
    {
        $stats = Cache::remember('superadmin_dashboard_stats', 600, function () {
            return [
                'total_properties' => Property::count(),
                'verified_properties' => Property::verified()->count(),
                'pending_properties' => Property::where('verification_status', 'pending')->count(),
                'total_owners' => User::where('role', 'owner')->count(),
                'total_tenants' => User::where('role', 'tenant')->count(),
                'active_tenancies' => Contract::where('status', 'active')->count(),
                'total_rooms' => Room::where('status', 'active')->count(),
                'available_beds' => Bed::where('status', 'available')->count(),
                'occupied_beds' => Bed::where('status', 'occupied')->count(),
                'pending_applications' => Application::where('status', 'pending')->count(),
                'monthly_revenue' => Payment::where('status', 'paid')
                    ->whereMonth('paid_date', now()->month)
                    ->whereYear('paid_date', now()->year)
                    ->sum('amount'),
                'outstanding_payments' => Payment::whereIn('status', ['pending', 'overdue'])->sum('amount'),
                'expiring_contracts' => Contract::where('status', 'active')
                    ->where('end_date', '<=', now()->addDays(30))
                    ->count(),
                'maintenance_requests' => MaintenanceRequest::whereIn('status', ['submitted', 'assigned', 'in_progress'])->count(),
            ];
        });

        // Load recent activity logs and notifications
        $recentProperties = Property::with(['owner', 'propertyType'])->latest()->limit(5)->get();
        $recentApplications = Application::with(['tenant', 'property'])->latest()->limit(5)->get();
        $recentMaintenance = MaintenanceRequest::with(['tenant', 'property'])->latest()->limit(5)->get();

        return view('dashboard.superadmin', compact('stats', 'recentProperties', 'recentApplications', 'recentMaintenance'));
    }

    /**
     * Property Owner Dashboard. Statistics are cached per owner for 5 minutes.
     */
    protected function ownerDashboard()
    {
        $ownerId = auth()->id();

        $stats = Cache::remember("owner_dashboard_stats_{$ownerId}", 300, function () use ($ownerId) {
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

            // Total revenue
            $revenue = Payment::whereIn('property_id', $propertyIds)
                ->where('status', 'paid')
                ->sum('amount');

            $outstanding = Payment::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount');

            // Total beds vs available beds (for per_bed)
            $totalBeds = Bed::whereHas('room', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->count();
            
            $availableBeds = Bed::whereHas('room', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->where('status', 'available')->count();

            // Count rooms without active contract (for per_room)
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

            $totalBuildings = \App\Models\Building::whereIn('property_id', $propertyIds)->count();
            $totalFloors = \App\Models\Floor::whereHas('building', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->count();

            return [
                'total_properties' => count($propertyIds),
                'total_buildings' => $totalBuildings,
                'total_floors' => $totalFloors,
                'occupancy_rate' => $occupancyRate,
                'available_rooms' => Room::whereIn('property_id', $propertyIds)->where('status', 'active')->count() - $occupiedRooms,
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

        $propertyIds = Property::where('owner_id', $ownerId)->pluck('id')->toArray();
        $recentApplications = !empty($propertyIds) ? Application::with(['tenant', 'property'])->whereIn('property_id', $propertyIds)->latest()->limit(5)->get() : collect();
        $recentMaintenance = !empty($propertyIds) ? MaintenanceRequest::with(['tenant', 'property'])->whereIn('property_id', $propertyIds)->latest()->limit(5)->get() : collect();
        $recentPayments = !empty($propertyIds) ? Payment::with(['tenant', 'property'])->whereIn('property_id', $propertyIds)->latest()->limit(5)->get() : collect();

        return view('dashboard.owner', compact('stats', 'recentApplications', 'recentMaintenance', 'recentPayments'));
    }

    /**
     * Manager / Receptionist Dashboard.
     */
    protected function managerDashboard()
    {
        $managerId = auth()->id();
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $stats = Cache::remember("manager_dashboard_stats_{$managerId}", 300, function () use ($assignedPropertyIds) {
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
                'available_rooms' => Room::whereIn('property_id', $assignedPropertyIds)->where('status', 'active')->count() - $occupiedRooms,
                'available_beds' => $availableBeds,
                'total_tenants' => Contract::whereIn('property_id', $assignedPropertyIds)->where('status', 'active')->count(),
                'pending_applications' => Application::whereIn('property_id', $assignedPropertyIds)->where('status', 'pending')->count(),
                'maintenance_requests' => MaintenanceRequest::whereIn('property_id', $assignedPropertyIds)
                    ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
                    ->count(),
            ];
        });

        $recentApplications = !empty($assignedPropertyIds) ? Application::with(['tenant', 'property'])->whereIn('property_id', $assignedPropertyIds)->latest()->limit(5)->get() : collect();
        $recentMaintenance = !empty($assignedPropertyIds) ? MaintenanceRequest::with(['tenant', 'property'])->whereIn('property_id', $assignedPropertyIds)->latest()->limit(5)->get() : collect();
        
        return view('dashboard.manager', compact('stats', 'recentApplications', 'recentMaintenance'));
    }

    /**
     * Tenant Dashboard. Runs fast, doesn't need aggressive caching because statistics are unique to individual.
     */
    protected function tenantDashboard()
    {
        $tenantId = auth()->id();

        // Get current active tenancy (contracts)
        $activeContract = Contract::with(['property', 'room', 'bed'])
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['accepted', 'active'])
            ->latest()
            ->first();

        // Next payment due
        $nextPayment = Payment::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();

        // Count pending applications
        $pendingApplicationsCount = Application::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        // Count open maintenance requests
        $openMaintenanceCount = MaintenanceRequest::where('tenant_id', $tenantId)
            ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
            ->count();

        // Load dynamic details for current property
        $paymentsHistory = Payment::where('tenant_id', $tenantId)->latest()->limit(5)->get();
        $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenantId)->latest()->limit(5)->get();

        return view('dashboard.tenant', compact(
            'activeContract',
            'nextPayment',
            'pendingApplicationsCount',
            'openMaintenanceCount',
            'paymentsHistory',
            'maintenanceRequests'
        ));
    }
}
