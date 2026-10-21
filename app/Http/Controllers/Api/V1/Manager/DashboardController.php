<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ContractResource;
use App\Http\Resources\Api\V1\MaintenanceResource;
use App\Models\Contract;
use App\Models\MaintenanceRequest;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get operational manager dashboard stats and today's schedule.
     */
    public function index(Request $request): JsonResponse
    {
        $managerId = $request->user()->id;
        $stats = $this->reportService->getManagerStats($managerId);

        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        // Today's arrivals (contracts accepted starting today or in future)
        $arrivals = Contract::with(['tenant', 'property', 'room', 'bed'])
            ->whereIn('property_id', $assignedPropertyIds)
            ->where('status', 'accepted')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->latest()
            ->limit(5)
            ->get();

        // Today's departures (active contracts ending today or past due)
        $departures = Contract::with(['tenant', 'property', 'room', 'bed'])
            ->whereIn('property_id', $assignedPropertyIds)
            ->where('status', 'active')
            ->whereDate('end_date', '<=', now()->toDateString())
            ->latest()
            ->limit(5)
            ->get();

        // Open maintenance
        $openMaintenance = MaintenanceRequest::with(['tenant', 'property', 'room', 'bed'])
            ->whereIn('property_id', $assignedPropertyIds)
            ->whereIn('status', ['submitted', 'assigned', 'in_progress'])
            ->latest()
            ->limit(5)
            ->get();

        return $this->successResponse([
            'stats' => $stats,
            'today_arrivals' => ContractResource::collection($arrivals),
            'today_departures' => ContractResource::collection($departures),
            'open_maintenance' => MaintenanceResource::collection($openMaintenance),
        ], 'Manager dashboard data retrieved.');
    }
}
