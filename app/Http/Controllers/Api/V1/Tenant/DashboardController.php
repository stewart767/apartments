<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ContractResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Resources\Api\V1\MaintenanceResource;
use App\Models\Payment;
use App\Models\MaintenanceRequest;
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
        $tenantId = $request->user()->id;
        $data = $this->reportService->getTenantDashboardData($tenantId);

        $recentPayments = Payment::where('tenant_id', $tenantId)->latest()->limit(5)->get();
        $recentMaintenance = MaintenanceRequest::where('tenant_id', $tenantId)->latest()->limit(5)->get();

        return $this->successResponse([
            'active_contract' => $data['active_contract'] ? new ContractResource($data['active_contract']) : null,
            'next_payment' => $data['next_payment'] ? new PaymentResource($data['next_payment']) : null,
            'pending_applications_count' => $data['pending_applications_count'],
            'open_maintenance_count' => $data['open_maintenance_count'],
            'recent_payments' => PaymentResource::collection($recentPayments),
            'recent_maintenance' => MaintenanceResource::collection($recentMaintenance),
        ], 'Tenant dashboard data retrieved.');
    }
}
