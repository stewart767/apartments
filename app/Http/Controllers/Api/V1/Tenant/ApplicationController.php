<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ApplicationResource;
use App\Models\Application;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ApplicationController extends ApiController
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * List applications for the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $applications = Application::with(['property.primaryImage', 'room', 'bed'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($applications, ApplicationResource::class, 'Applications retrieved successfully.');
    }

    /**
     * Submit a new booking reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_id' => 'nullable',
            'bed_id' => 'nullable|exists:beds,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'tenant_notes' => 'nullable|string|max:500',
        ]);

        try {
            $data = $request->only(['property_id', 'room_id', 'bed_id', 'start_date', 'end_date', 'tenant_notes']);
            
            // Normalize "all" for whole property
            if (isset($data['room_id']) && $data['room_id'] === 'all') {
                $data['room_id'] = null;
            }

            $application = $this->bookingService->createApplication($request->user(), $data);

            return $this->successResponse(
                new ApplicationResource($application->load(['property.primaryImage', 'room', 'bed'])),
                'Booking application submitted successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * View specific booking application details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $application = Application::with(['property.primaryImage', 'room', 'bed'])
            ->where('tenant_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(new ApplicationResource($application), 'Application details retrieved.');
    }

    /**
     * Cancel a pending application.
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $application = Application::where('tenant_id', $request->user()->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $this->bookingService->processApplication($application, 'cancelled', 'Cancelled by tenant.');

        return $this->successResponse(null, 'Application cancelled successfully.');
    }
}
