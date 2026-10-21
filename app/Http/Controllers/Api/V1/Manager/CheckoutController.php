<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ContractResource;
use App\Models\Contract;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class CheckoutController extends ApiController
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * List active contracts eligible for check-out.
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
            ->where('status', 'active')
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($contracts, ContractResource::class, 'Contracts eligible for check-out retrieved.');
    }

    /**
     * Execute tenant check-out with damage calculation and deposit refund.
     */
    public function checkOut(Request $request, $contractId): JsonResponse
    {
        $request->validate([
            'check_out_date' => 'required|date',
            'room_condition_notes' => 'nullable|string|max:500',
            'damage_charges' => 'required|numeric|min:0',
            'deposit_refunded' => 'required|numeric|min:0',
            'outstanding_settled' => 'required|boolean',
            'checkout_property_assets' => 'nullable|array',
            'checkout_tenant_assets' => 'nullable|array',
        ]);

        $managerId = $request->user()->id;
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $contract = Contract::whereIn('property_id', $assignedPropertyIds)->findOrFail($contractId);

        $checkOut = $this->checkoutService->processCheckout($contract, $request->all(), $request->user());

        return $this->successResponse(
            $checkOut,
            'Tenant check-out logged successfully. Unit allocation freed and deposit calculation recorded.'
        );
    }
}
