<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Contract;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ReportController extends ApiController
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get owner financial and occupancy reports.
     */
    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        $stats = $this->reportService->getOwnerStats($ownerId);

        $propertyIds = Property::where('owner_id', $ownerId)->pluck('id')->toArray();

        // Monthly revenue breakdown for current year
        $monthlyRevenue = Payment::whereIn('property_id', $propertyIds)
            ->where('status', 'paid')
            ->whereYear('paid_date', now()->year)
            ->selectRaw('MONTH(paid_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return $this->successResponse([
            'summary' => $stats,
            'monthly_revenue' => $monthlyRevenue,
        ], 'Owner reports retrieved successfully.');
    }
}
