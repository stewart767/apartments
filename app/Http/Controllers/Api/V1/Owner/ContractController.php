<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ContractResource;
use App\Models\Contract;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class ContractController extends ApiController
{
    /**
     * List contracts for owned properties.
     */
    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        $query = Contract::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property.primaryImage', 'tenant', 'room', 'bed', 'checkIn', 'checkOut'])
        ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $contracts = $query->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($contracts, ContractResource::class, 'Contracts retrieved.');
    }

    /**
     * Show contract details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $contract = Contract::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })
        ->with(['property.owner', 'tenant.tenantDetail.institution', 'room', 'bed', 'checkIn', 'checkOut'])
        ->findOrFail($id);

        return $this->successResponse(new ContractResource($contract), 'Contract details retrieved.');
    }

    /**
     * Download contract PDF.
     */
    public function downloadPdf(Request $request, $id)
    {
        $contract = Contract::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })
        ->with(['tenant.tenantDetail.institution', 'property.owner', 'room', 'bed'])
        ->findOrFail($id);

        $pdf = Pdf::loadView('contracts.pdf', compact('contract'));
        return $pdf->download("Lease_Agreement_{$contract->contract_number}.pdf");
    }
}
