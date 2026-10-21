<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends ApiController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * List payment history and due payments for the tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with(['property', 'contract'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($payments, PaymentResource::class, 'Payments retrieved successfully.');
    }

    /**
     * View payment details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $payment = Payment::with(['property', 'contract'])
            ->where('tenant_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(new PaymentResource($payment), 'Payment details retrieved.');
    }

    /**
     * Submit payment proof receipt.
     */
    public function submitProof(Request $request, $id): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'required|string|max:100|unique:payments,payment_reference,' . $id,
            'receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = Payment::where('tenant_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $updatedPayment = $this->paymentService->submitPaymentProof(
            $payment,
            $request->only(['payment_method', 'payment_reference', 'notes']),
            $request->file('receipt')
        );

        return $this->successResponse(
            new PaymentResource($updatedPayment->load(['property', 'contract'])),
            'Payment proof uploaded successfully and is awaiting owner verification.'
        );
    }
}
