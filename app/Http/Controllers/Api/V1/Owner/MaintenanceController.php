<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\MaintenanceResource;
use App\Models\MaintenanceRequest;
use App\Models\User;
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
     * List maintenance tickets for owned properties.
     */
    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;

        $query = MaintenanceRequest::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property', 'tenant', 'room', 'bed', 'images', 'assignedTo'])
        ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tickets = $query->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($tickets, MaintenanceResource::class, 'Maintenance tickets retrieved.');
    }

    /**
     * Show maintenance ticket details & available staff.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $ticket = MaintenanceRequest::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })
        ->with(['property', 'tenant', 'room', 'bed', 'images', 'assignedTo'])
        ->findOrFail($id);

        $staffMembers = User::where(function ($query) use ($ticket) {
            $query->where('role', 'manager')
                ->whereHas('managedProperties', fn($q) => $q->where('properties.id', $ticket->property_id));
        })
        ->orWhere('id', $ticket->property->owner_id)
        ->get(['id', 'name', 'email', 'role']);

        return $this->successResponse([
            'ticket' => new MaintenanceResource($ticket),
            'available_staff' => $staffMembers,
        ], 'Maintenance ticket details retrieved.');
    }

    /**
     * Assign maintenance ticket to a staff member.
     */
    public function assign(Request $request, $id): JsonResponse
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = MaintenanceRequest::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($id);

        $assignedTicket = $this->maintenanceService->assignTicket(
            $ticket,
            (int) $request->input('assigned_to'),
            $request->user()
        );

        return $this->successResponse(
            new MaintenanceResource($assignedTicket),
            'Maintenance ticket assigned successfully.'
        );
    }

    /**
     * Resolve a maintenance ticket.
     */
    public function resolve(Request $request, $id): JsonResponse
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $ticket = MaintenanceRequest::whereHas('property', function ($q) use ($request) {
            $q->where('owner_id', $request->user()->id);
        })->findOrFail($id);

        $resolvedTicket = $this->maintenanceService->resolveTicket(
            $ticket,
            $request->input('resolution_notes'),
            $request->user()
        );

        return $this->successResponse(
            new MaintenanceResource($resolvedTicket),
            'Maintenance ticket marked as resolved.'
        );
    }
}
