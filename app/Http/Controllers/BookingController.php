<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display bookings for the logged-in tenant.
     */
    public function tenantApplications()
    {
        $tenantId = auth()->id();
        $applications = Application::with(['property.primaryImage', 'room', 'bed'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);

        return view('tenant.applications.index', compact('applications'));
    }

    /**
     * Display bookings for the property owner.
     */
    public function ownerApplications()
    {
        $ownerId = auth()->id();
        
        $applications = Application::whereHas('property', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })
        ->with(['property', 'tenant', 'room', 'bed'])
        ->latest()
        ->paginate(10);

        return view('owner.applications.index', compact('applications'));
    }

    /**
     * Display bookings for the property manager.
     */
    public function managerApplications()
    {
        $managerId = auth()->id();
        
        $applications = Application::whereHas('property.managers', function ($q) use ($managerId) {
            $q->where('users.id', $managerId);
        })
        ->with(['property', 'tenant', 'room', 'bed'])
        ->latest()
        ->paginate(10);

        return view('manager.applications.index', compact('applications'));
    }

    /**
     * Display details of a booking for review (Owner/Manager view).
     */
    public function showApplication($id)
    {
        $application = Application::with(['property', 'tenant.tenantDetail.institution', 'room', 'bed'])
            ->findOrFail($id);

        $this->authorizePropertyManagement($application);

        // Determine view path based on role
        $viewPath = auth()->user()->isOwner() ? 'owner.applications.show' : 'manager.applications.show';

        return view($viewPath, compact('application'));
    }

    /**
     * Process application approval or rejection.
     */
    public function processApplication(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'owner_notes' => 'nullable|string|max:500',
        ]);

        $application = Application::findOrFail($id);
        $this->authorizePropertyManagement($application);

        $this->bookingService->processApplication(
            $application, 
            $request->input('status'), 
            $request->input('owner_notes')
        );

        $dashboardRoute = auth()->user()->isOwner() ? 'owner.applications' : 'manager.applications';

        return redirect()->route($dashboardRoute)
            ->with('success', "Booking application was successfully updated to " . $request->input('status') . "!");
    }

    /**
     * Helper to verify role access permissions.
     */
    protected function authorizePropertyManagement(Application $application)
    {
        $user = auth()->user();
        if ($user->isSuperadmin()) {
            return true;
        }
        if ($user->isOwner() && $application->property->owner_id === $user->id) {
            return true;
        }
        if ($user->isManager() || $user->isReceptionist()) {
            $hasAssignment = \DB::table('property_user')
                ->where('property_id', $application->property_id)
                ->where('user_id', $user->id)
                ->exists();
            if ($hasAssignment) {
                return true;
            }
        }

        abort(403, 'Unauthorized property management operation.');
    }
}
