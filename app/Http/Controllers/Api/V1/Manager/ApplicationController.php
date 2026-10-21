<?php

namespace App\Http\Controllers\Api\V1\Manager;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\ApplicationResource;
use App\Models\Application;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ApplicationController extends ApiController
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * List applications for assigned properties.
     */
    public function index(Request $request): JsonResponse
    {
        $managerId = $request->user()->id;

        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $query = Application::whereIn('property_id', $assignedPropertyIds)
            ->with(['property.primaryImage', 'tenant.tenantDetail.institution', 'room', 'bed'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($applications, ApplicationResource::class, 'Applications retrieved.');
    }

    /**
     * Show application details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $managerId = $request->user()->id;

        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $application = Application::whereIn('property_id', $assignedPropertyIds)
            ->with(['property.primaryImage', 'tenant.tenantDetail.institution', 'room', 'bed'])
            ->findOrFail($id);

        return $this->successResponse(new ApplicationResource($application), 'Application details retrieved.');
    }

    /**
     * Process application approval or rejection.
     */
    public function process(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'owner_notes' => 'nullable|string|max:500',
        ]);

        $managerId = $request->user()->id;
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $application = Application::whereIn('property_id', $assignedPropertyIds)->findOrFail($id);

        $status = $request->input('status');
        $this->bookingService->processApplication($application, $status, $request->input('owner_notes'));

        return $this->successResponse(
            new ApplicationResource($application->fresh(['property', 'tenant', 'room', 'bed'])),
            "Application has been {$status} successfully."
        );
    }
}
