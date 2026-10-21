<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends ApiController
{
    /**
     * List invoices for the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::with(['property', 'payments'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($invoices, InvoiceResource::class, 'Invoices retrieved successfully.');
    }

    /**
     * View invoice details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $invoice = Invoice::with(['property', 'payments'])
            ->where('tenant_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(new InvoiceResource($invoice), 'Invoice details retrieved.');
    }
}
