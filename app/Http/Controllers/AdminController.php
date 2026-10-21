<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Application;
use App\Models\User;
use App\Models\Amenity;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Display a listing of all properties.
     */
    public function properties(Request $request)
    {
        $query = Property::with(['owner', 'propertyType', 'region', 'district', 'ward', 'primaryImage'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('property_name', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $properties = $query->paginate(15)->withQueryString();

        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Verify/approve/reject a property.
     */
    public function verifyProperty(Request $request, $id, PropertyService $propertyService)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,changes_requested',
            'notes' => 'nullable|string|max:1000',
        ]);

        $property = Property::findOrFail($id);

        $propertyService->changeVerificationStatus($property, $request->status, $request->notes);

        // Clear dashboard caches
        Cache::forget('superadmin_dashboard_stats');

        return back()->with('success', "Property status updated to {$request->status} successfully.");
    }

    /**
     * Display a listing of all applications.
     */
    public function applications(Request $request)
    {
        $query = Application::with(['property', 'tenant', 'room', 'bed'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('property', function($pq) use ($search) {
                      $pq->where('property_name', 'like', "%{$search}%");
                  });
            });
        }

        $applications = $query->paginate(15)->withQueryString();

        return view('admin.applications.index', compact('applications'));
    }

    /**
     * Display a listing of all users.
     */
    public function users(Request $request)
    {
        $query = User::with(['ownerDetail.region', 'ownerDetail.district', 'ownerDetail.ward'])
            ->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle status (active/suspended) of a user.
     */
    public function toggleUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "User account status changed to {$newStatus}.");
    }

    /**
     * Verify/approve/reject an owner onboarding profile.
     */
    public function verifyOwner(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($id);

        if (!$user->isOwner() || !$user->ownerDetail) {
            return back()->with('error', 'This user does not have an onboarding profile.');
        }

        $user->ownerDetail->update([
            'verification_status' => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
        ]);

        return back()->with('success', "Owner profile verification status updated to {$request->status} successfully.");
    }

    /**
     * Display a listing of all amenities.
     */
    public function amenities()
    {
        $amenities = Amenity::withCount(['properties', 'rooms'])->get();
        return view('admin.amenities.index', compact('amenities'));
    }

    /**
     * Store a new amenity.
     */
    public function storeAmenity(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:amenities,name',
            'icon' => 'nullable|string|max:255',
            'category' => 'required|in:property,room,general',
        ]);

        Amenity::create($request->only(['name', 'icon', 'category']));

        return back()->with('success', 'Amenity created successfully.');
    }

    /**
     * Delete an amenity.
     */
    public function destroyAmenity($id)
    {
        $amenity = Amenity::findOrFail($id);

        // Detach relations
        $amenity->properties()->detach();
        $amenity->rooms()->detach();
        
        $amenity->delete();

        return back()->with('success', 'Amenity deleted successfully.');
    }
}
