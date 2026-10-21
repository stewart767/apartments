<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ApplicationResource;
use App\Http\Resources\Api\V1\MaintenanceResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Property;
use App\Models\Application;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        $stats = $this->reportService->getOwnerStats($ownerId);

        $propertyIds = Property::where('owner_id', $ownerId)->pluck('id')->toArray();
        $recentApplications = !empty($propertyIds) ? Application::with(['tenant', 'property'])->whereIn('property_id', $propertyIds)->latest()->limit(5)->get() : collect();
        $recentMaintenance = !empty($propertyIds) ? MaintenanceRequest::with(['tenant', 'property'])->whereIn('property_id', $propertyIds)->latest()->limit(5)->get() : collect();
        $recentPayments = !empty($propertyIds) ? Payment::with(['tenant', 'property'])->whereIn('property_id', $propertyIds)->latest()->limit(5)->get() : collect();

        return $this->successResponse([
            'stats' => $stats,
            'recent_applications' => ApplicationResource::collection($recentApplications),
            'recent_maintenance' => MaintenanceResource::collection($recentMaintenance),
            'recent_payments' => PaymentResource::collection($recentPayments),
        ], 'Owner dashboard data retrieved.');
    }
}
