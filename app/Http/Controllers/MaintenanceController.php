<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Contract;
use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    protected $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Tenant views their tickets list.
     */
    public function tenantIndex()
    {
        $tickets = MaintenanceRequest::with(['property', 'room', 'bed'])
            ->where('tenant_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('tenant.maintenance.index', compact('tickets'));
    }

    /**
     * Tenant views file repairs form.
     */
    public function tenantCreate()
    {
        $activeLease = Contract::where('tenant_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$activeLease) {
            return redirect()->route('tenant.maintenance')
                ->with('error', 'You must have an active lease check-in to submit maintenance repair tickets.');
        }

        return view('tenant.maintenance.create', compact('activeLease'));
    }

    /**
     * Tenant submits a repairs ticket.
     */
    public function tenantStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:1000',
            'priority' => 'required|in:low,medium,high,emergency',
            'photos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $activeLease = Contract::with(['property.owner', 'tenant'])
            ->where('tenant_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        $photos = $request->file('photos', []);
        $ticket = $this->maintenanceService->createTicket(
            auth()->user(),
            $activeLease,
            $request->only(['title', 'description', 'priority']),
            is_array($photos) ? $photos : []
        );

        return redirect()->route('tenant.maintenance')
            ->with('success', "Maintenance request ticket #{$ticket->ticket_number} submitted successfully.");
    }

    /**
     * Owner views maintenance requests.
     */
    public function ownerIndex()
    {
        $ownerId = auth()->id();
        $tickets = MaintenanceRequest::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property', 'tenant', 'room', 'bed', 'assignedTo'])
        ->latest()
        ->paginate(10);

        return view('owner.maintenance.index', compact('tickets'));
    }

    /**
     * Manager views maintenance requests.
     */
    public function managerIndex()
    {
        $managerId = auth()->id();
        $assignedPropertyIds = DB::table('property_user')
            ->where('user_id', $managerId)
            ->pluck('property_id')
            ->toArray();

        $tickets = collect();
        if (!empty($assignedPropertyIds)) {
            $tickets = MaintenanceRequest::whereIn('property_id', $assignedPropertyIds)
                ->with(['property', 'tenant', 'room', 'bed', 'assignedTo'])
                ->latest()
                ->paginate(10);
        } else {
            $tickets = MaintenanceRequest::where('id', 0)->paginate(10);
        }

        return view('manager.maintenance.index', compact('tickets'));
    }

    /**
     * View details of a specific maintenance ticket.
     */
    public function showTicket($id)
    {
        $ticket = MaintenanceRequest::with(['property.owner', 'tenant', 'room', 'bed', 'images', 'assignedTo'])
            ->findOrFail($id);

        $user = auth()->user();
        
        if ($user->isTenant() && $ticket->tenant_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if ($user->isOwner() && $ticket->property->owner_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if ($user->isManager() || $user->isReceptionist()) {
            $hasAssignment = DB::table('property_user')
                ->where('property_id', $ticket->property_id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasAssignment) {
                abort(403, 'Unauthorized.');
            }
        }

        $staffMembers = User::where(function($query) use ($ticket) {
            $query->where('role', 'manager')
                ->whereHas('managedProperties', function($q) use ($ticket) {
                    $q->where('properties.id', $ticket->property_id);
                });
        })->orWhere('id', $ticket->property->owner_id)
        ->get();

        $viewPath = $user->isOwner() ? 'owner.maintenance.show' : 'manager.maintenance.show';

        return view($viewPath, compact('ticket', 'staffMembers'));
    }

    /**
     * Assign maintenance ticket to a staff member.
     */
    public function assignTicket(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = MaintenanceRequest::with('property')->findOrFail($id);
        $this->maintenanceService->assignTicket($ticket, (int) $request->input('assigned_to'), auth()->user());

        return back()->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Resolve a maintenance ticket.
     */
    public function resolveTicket(Request $request, $id)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $ticket = MaintenanceRequest::with('property')->findOrFail($id);
        $this->maintenanceService->resolveTicket($ticket, $request->input('resolution_notes'), auth()->user());

        return back()->with('success', 'Maintenance request marked as resolved.');
    }
}
