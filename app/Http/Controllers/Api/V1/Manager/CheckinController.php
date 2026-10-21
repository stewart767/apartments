<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ContractResource;
use App\Models\Contract;
use App\Services\CheckinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class CheckinController extends ApiController
{
    protected $checkinService;

    public function __construct(CheckinService $checkinService)
    {
        $this->checkinService = $checkinService;
    }

    /**
     * List contracts awaiting check-in.
     */
    public function index(Request $request): JsonResponse
    {
        $managerId = $request->user()->id;
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $contracts = Contract::with(['property', 'tenant.tenantAssets', 'room.propertyAssets', 'bed', 'checkIn'])
            ->whereIn('property_id', $assignedPropertyIds)
            ->where('status', 'accepted')
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($contracts, ContractResource::class, 'Contracts awaiting check-in retrieved.');
    }

    /**
     * Execute tenant check-in.
     */
    public function checkIn(Request $request, $contractId): JsonResponse
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'deposit_received' => 'required|numeric|min:0',
            'room_condition_notes' => 'nullable|string|max:500',
            'keys_count' => 'required|integer|min:1',
            'property_assets' => 'nullable|array',
            'tenant_assets' => 'nullable|array',
        ]);

        $managerId = $request->user()->id;
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $contract = Contract::whereIn('property_id', $assignedPropertyIds)->findOrFail($contractId);

        $checkIn = $this->checkinService->processCheckin($contract, $request->all(), $request->user());

        return $this->successResponse(
            $checkIn,
            'Tenant check-in completed successfully. Unit activated and baseline inventory recorded.'
        );
    }
}
