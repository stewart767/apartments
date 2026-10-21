<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Contract;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends ApiController
{
    /**
     * Submit a review for a completed or active contract.
     */
    public function store(Request $request, $contractId): JsonResponse
    {
        $request->validate([
            'cleanliness' => 'required|integer|min:1|max:5',
            'security' => 'required|integer|min:1|max:5',
            'water' => 'required|integer|min:1|max:5',
            'electricity' => 'required|integer|min:1|max:5',
            'management' => 'required|integer|min:1|max:5',
            'location' => 'required|integer|min:1|max:5',
            'value_for_money' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $contract = Contract::where('tenant_id', $request->user()->id)->findOrFail($contractId);

        $review = Review::updateOrCreate(
            [
                'contract_id' => $contract->id,
                'tenant_id' => $request->user()->id,
            ],
            array_merge($request->only([
                'cleanliness', 'security', 'water', 'electricity',
                'management', 'location', 'value_for_money', 'comment'
            ]), [
                'property_id' => $contract->property_id,
                'is_approved' => true,
            ])
        );

        return $this->successResponse(
            new ReviewResource($review->load('tenant')),
            'Review submitted successfully.',
            201
        );
    }
}
