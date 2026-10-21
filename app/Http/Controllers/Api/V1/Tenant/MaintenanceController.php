<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\MaintenanceResource;
use App\Models\MaintenanceRequest;
use App\Models\Contract;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MaintenanceController extends ApiController
{
    protected $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * List maintenance tickets for the authenticated tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = MaintenanceRequest::with(['property', 'room', 'bed', 'images', 'assignedTo'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($tickets, MaintenanceResource::class, 'Maintenance tickets retrieved successfully.');
    }

    /**
     * Create a new maintenance ticket with photo uploads.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:1000',
            'priority' => 'required|in:low,medium,high,emergency',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $activeLease = Contract::with(['property.owner', 'tenant'])
            ->where('tenant_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$activeLease) {
            return $this->errorResponse('You must have an active lease check-in to submit maintenance repair tickets.', 422);
        }

        $photos = $request->file('photos', []);
        $ticket = $this->maintenanceService->createTicket(
            $request->user(),
            $activeLease,
            $request->only(['title', 'description', 'priority']),
            is_array($photos) ? $photos : []
        );

        return $this->successResponse(
            new MaintenanceResource($ticket->load(['property', 'room', 'bed', 'images'])),
            "Maintenance request ticket #{$ticket->ticket_number} created successfully.",
            201
        );
    }

    /**
     * View specific maintenance ticket details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $ticket = MaintenanceRequest::with(['property', 'room', 'bed', 'images', 'assignedTo'])
            ->where('tenant_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(new MaintenanceResource($ticket), 'Maintenance ticket details retrieved.');
    }
}
