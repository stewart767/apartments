<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Bed;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports for Property Owners.
     */
    public function ownerReports()
    {
        $ownerId = auth()->id();

        $data = Cache::remember("owner_reports_{$ownerId}", 600, function () use ($ownerId) {
            $propertyIds = Property::where('owner_id', $ownerId)->pluck('id')->toArray();

            if (empty($propertyIds)) {
                return [
                    'total_revenue' => 0,
                    'outstanding_balance' => 0,
                    'occupancy_rate' => 0,
                    'total_rooms' => 0,
                    'occupied_rooms' => 0,
                    'total_beds' => 0,
                    'occupied_beds' => 0,
                    'open_tickets' => 0,
                    'resolved_tickets' => 0,
                    'revenue_by_property' => [],
                    'priority_counts' => [],
                ];
            }

            // Financials: Gross Rent vs Deposit Liabilities
            $rentInvoiced = Payment::whereIn('property_id', $propertyIds)
                ->where('payment_type', 'rent')
                ->sum('amount');

            $rentCollected = Payment::whereIn('property_id', $propertyIds)
                ->where('payment_type', 'rent')
                ->where('status', 'paid')
                ->sum('amount');

            $outstandingRent = Payment::whereIn('property_id', $propertyIds)
                ->where('payment_type', 'rent')
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount');

            $contractIds = Contract::whereIn('property_id', $propertyIds)->pluck('id')->toArray();
            $grossDeposits = Payment::whereIn('property_id', $propertyIds)
                ->where('payment_type', 'deposit')
                ->where('status', 'paid')
                ->sum('amount');

            $damageCharges = !empty($contractIds) ? (float) \App\Models\CheckOut::whereIn('contract_id', $contractIds)->sum('damage_charges') : 0.00;
            $depositRefunds = !empty($contractIds) ? (float) \App\Models\CheckOut::whereIn('contract_id', $contractIds)->sum('deposit_refunded') : 0.00;
            $depositsHeld = max(0, $grossDeposits - ($damageCharges + $depositRefunds));

            // Total Revenue across all categories
            $totalRevenue = Payment::whereIn('property_id', $propertyIds)
                ->where('status', 'paid')
                ->sum('amount');

            // Outstanding Balance
            $outstanding = Payment::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount');

            // Occupancy
            $totalRooms = Room::whereIn('property_id', $propertyIds)->count();
            $occupiedRooms = Contract::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['accepted', 'active'])
                ->count();

            $totalBeds = Bed::whereHas('room', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->count();

            $occupiedBeds = Bed::whereHas('room', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })->where('status', 'occupied')->count();

            $occupancyRate = 0;
            if ($totalBeds > 0) {
                $occupancyRate = round(($occupiedBeds / $totalBeds) * 100, 1);
            } elseif ($totalRooms > 0) {
                $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 1);
            }

            // Maintenance load
            $openTickets = MaintenanceRequest::whereIn('property_id', $propertyIds)
                ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
                ->count();
            
            $resolvedTickets = MaintenanceRequest::whereIn('property_id', $propertyIds)
                ->where('status', 'resolved')
                ->count();

            // Revenue by Property
            $revenueByProperty = Property::where('owner_id', $ownerId)
                ->get()
                ->map(function($prop) {
                    $paidSum = Payment::where('property_id', $prop->id)->where('status', 'paid')->sum('amount');
                    return [
                        'name' => $prop->property_name,
                        'revenue' => $paidSum,
                    ];
                });

            // Maintenance by Priority
            $priorityCounts = MaintenanceRequest::whereIn('property_id', $propertyIds)
                ->select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->toArray();

            return [
                'total_revenue' => $totalRevenue,
                'rent_invoiced' => $rentInvoiced,
                'rent_collected' => $rentCollected,
                'outstanding_rent' => $outstandingRent,
                'deposits_held' => $depositsHeld,
                'damage_deductions' => $damageCharges,
                'deposit_refunds' => $depositRefunds,
                'outstanding_balance' => $outstanding,
                'occupancy_rate' => $occupancyRate,
                'total_rooms' => $totalRooms,
                'occupied_rooms' => $occupiedRooms,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'open_tickets' => $openTickets,
                'resolved_tickets' => $resolvedTickets,
                'revenue_by_property' => $revenueByProperty,
                'priority_counts' => $priorityCounts,
            ];
        });

        return view('reports.owner', compact('data'));
    }

    /**
     * Display reports for Superadmin.
     */
    public function adminReports()
    {
        $data = Cache::remember('admin_reports', 600, function () {
            // Platform SaaS Revenue (distinct from tenant payments)
            $totalSaasRevenue = \App\Models\SubscriptionInvoice::where('status', 'paid')->sum('amount');
            $activeSubscriptions = \App\Models\Subscription::where('status', 'active')->count();

            // System tenant marketplace rental volume
            $totalTenantPayments = Payment::where('status', 'paid')->sum('amount');
            $outstanding = Payment::whereIn('status', ['pending', 'overdue'])->sum('amount');

            // System total occupancy
            $totalBeds = Bed::count();
            $occupiedBeds = Bed::where('status', 'occupied')->count();
            $totalRooms = Room::count();
            
            $occupancyRate = 0;
            if ($totalBeds > 0) {
                $occupancyRate = round(($occupiedBeds / $totalBeds) * 100, 1);
            }

            // Properties status breakdown
            $propertyStatus = Property::select('verification_status', DB::raw('count(*) as total'))
                ->groupBy('verification_status')
                ->pluck('total', 'verification_status')
                ->toArray();

            // Users breakdown by roles
            $userRoles = User::select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->pluck('total', 'role')
                ->toArray();

            // Open maintenance requests
            $openTickets = MaintenanceRequest::whereIn('status', ['submitted', 'assigned', 'in_progress'])->count();
            $resolvedTickets = MaintenanceRequest::where('status', 'resolved')->count();

            return [
                'saas_subscription_revenue' => $totalSaasRevenue,
                'active_saas_subscriptions' => $activeSubscriptions,
                'total_revenue' => $totalTenantPayments,
                'outstanding_balance' => $outstanding,
                'occupancy_rate' => $occupancyRate,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'total_rooms' => $totalRooms,
                'property_status' => $propertyStatus,
                'user_roles' => $userRoles,
                'open_tickets' => $openTickets,
                'resolved_tickets' => $resolvedTickets,
            ];
        });

        return view('reports.admin', compact('data'));
    }
}
