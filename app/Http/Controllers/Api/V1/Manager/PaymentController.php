<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PaymentController extends ApiController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * List payments for assigned properties.
     */
    public function index(Request $request): JsonResponse
    {
        $managerId = $request->user()->id;
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $query = Payment::whereIn('property_id', $assignedPropertyIds)
            ->with(['property', 'tenant', 'contract'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $payments = $query->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($payments, PaymentResource::class, 'Payments retrieved.');
    }

    /**
     * Verify payment proof on assigned property.
     */
    public function verify(Request $request, $id): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        $managerId = $request->user()->id;
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $payment = Payment::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);

        $action = $request->input('action');
        $updatedPayment = $this->paymentService->verifyPayment($payment, $action, $request->input('notes'), $request->user());

        return $this->successResponse(
            new PaymentResource($updatedPayment->load(['property', 'tenant', 'contract'])),
            $action === 'approve' ? 'Payment verified and marked as PAID.' : 'Payment rejected.'
        );
    }
}
