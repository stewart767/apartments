<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ContractResource;
use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class ContractController extends ApiController
{
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * List lease contracts for the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::with(['property.primaryImage', 'property.owner', 'room', 'bed', 'checkIn', 'checkOut'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($contracts, ContractResource::class, 'Contracts retrieved successfully.');
    }

    /**
     * View details of a specific contract.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $contract = Contract::with(['property.owner', 'room', 'bed', 'checkIn', 'checkOut'])
            ->where('tenant_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(new ContractResource($contract), 'Contract details retrieved.');
    }

    /**
     * Digitally sign and accept contract terms.
     */
    public function accept(Request $request, $id): JsonResponse
    {
        $contract = Contract::where('tenant_id', $request->user()->id)
            ->where('status', 'pending_acceptance')
            ->findOrFail($id);

        $this->contractService->tenantAcceptContract($contract);

        return $this->successResponse(
            new ContractResource($contract->fresh(['property.owner', 'room', 'bed'])),
            'Contract accepted and signed successfully. Please proceed with payment.'
        );
    }

    /**
     * Download contract PDF.
     */
    public function downloadPdf(Request $request, $id)
    {
        $contract = Contract::with(['tenant.tenantDetail.institution', 'property.owner', 'room', 'bed'])
            ->where('tenant_id', $request->user()->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('contracts.pdf', compact('contract'));
        return $pdf->download("Lease_Agreement_{$contract->contract_number}.pdf");
    }
}
