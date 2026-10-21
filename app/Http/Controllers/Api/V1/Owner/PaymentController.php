<?php

namespace App\Http\Controllers\Api\V1\Owner;

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
     * List payments for owned properties.
     */
    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        $query = Payment::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property', 'tenant', 'contract'])
        ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $payments = $query->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($payments, PaymentResource::class, 'Payments retrieved.');
    }

    /**
     * Show payment details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $payment = Payment::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })
        ->with(['property', 'tenant', 'contract'])
        ->findOrFail($id);

        return $this->successResponse(new PaymentResource($payment), 'Payment details retrieved.');
    }

    /**
     * Verify payment proof (Approve or Reject).
     */
    public function verify(Request $request, $id): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = Payment::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($id);

        $action = $request->input('action');
        $updatedPayment = $this->paymentService->verifyPayment($payment, $action, $request->input('notes'), $request->user());

        return $this->successResponse(
            new PaymentResource($updatedPayment->load(['property', 'tenant', 'contract'])),
            $action === 'approve' ? 'Payment verified and marked as PAID.' : 'Payment rejected.'
        );
    }
}
