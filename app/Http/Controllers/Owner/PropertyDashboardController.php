<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Bed;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyDashboardController extends Controller
{
    /**
     * Check if authenticated user is authorized to manage the property.
     */
    protected function checkAuthorization(Property $property)
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return;
        }

        if ($property->owner_id === $user->id) {
            return;
        }

        // Check if assigned in pivot table
        $isAssigned = DB::table('property_user')
            ->where('property_id', $property->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'You are not authorized to manage this property.');
        }
    }

    public function index($id)
    {
        $property = Property::with(['propertyType', 'region', 'district', 'ward'])->findOrFail($id);
        $this->checkAuthorization($property);

        // Stats calculation
        $totalRooms = Room::where('property_id', $property->id)->count();
        $totalBeds = Bed::whereHas('room', function($q) use ($property) {
            $q->where('property_id', $property->id);
        })->count();

        $occupiedBeds = Bed::whereHas('room', function($q) use ($property) {
            $q->where('property_id', $property->id);
        })->where('status', 'occupied')->count();

        $availableBeds = Bed::whereHas('room', function($q) use ($property) {
            $q->where('property_id', $property->id);
        })->where('status', 'available')->count();

        // Occupancy percentage
        $occupancyRate = 0;
        if ($totalBeds > 0) {
            $occupancyRate = round(($occupiedBeds / $totalBeds) * 100, 1);
        } elseif ($totalRooms > 0) {
            $occupiedRooms = Room::where('property_id', $property->id)->where('status', 'active')->where('occupancy', '>', 0)->count();
            $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 1);
        }

        // Expected, Collected, Outstanding Revenue
        $expectedRevenue = Invoice::where('property_id', $property->id)->sum('amount');
        $collectedRevenue = Payment::where('property_id', $property->id)->where('status', 'paid')->sum('amount');
        $outstandingRevenue = Invoice::where('property_id', $property->id)->whereIn('status', ['unpaid', 'overdue'])->sum('amount');

        // Recent contracts
        $recentContracts = Contract::with(['tenant', 'room'])
            ->where('property_id', $property->id)
            ->latest()
            ->limit(5)
            ->get();

        // Recent maintenance requests
        $recentMaintenance = \App\Models\MaintenanceRequest::with(['tenant', 'room'])
            ->where('property_id', $property->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('owner.properties.dashboard.index', compact(
            'property', 'totalRooms', 'totalBeds', 'occupiedBeds', 'availableBeds',
            'occupancyRate', 'expectedRevenue', 'collectedRevenue', 'outstandingRevenue',
            'recentContracts', 'recentMaintenance'
        ));
    }

    public function buildingsFloors($id)
    {
        $property = Property::with(['buildings.floors'])->findOrFail($id);
        $this->checkAuthorization($property);

        return view('owner.properties.dashboard.buildings', compact('property'));
    }

    public function storeBuilding(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'building_name' => 'required|string|max:255',
        ]);

        Building::create([
            'property_id' => $property->id,
            'building_name' => $request->building_name,
            'building_code' => 'BLDG-' . strtoupper(substr($request->building_name, 0, 3)) . '-' . rand(100, 999),
            'status' => 'active',
        ]);

        return back()->with('success', 'Building added successfully.');
    }

    public function storeFloor(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'floor_number_name' => 'required|string|max:255',
        ]);

        Floor::create([
            'building_id' => $request->building_id,
            'floor_number_name' => $request->floor_number_name,
            'status' => 'active',
        ]);

        return back()->with('success', 'Floor added successfully.');
    }

    public function roomsBeds($id)
    {
        $property = Property::with(['buildings.floors', 'rooms.beds', 'rooms.roomType'])->findOrFail($id);
        $this->checkAuthorization($property);

        $roomTypes = RoomType::all();

        return view('owner.properties.dashboard.rooms', compact('property', 'roomTypes'));
    }

    public function storeRoom(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'room_number' => 'required|string|max:50',
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:daily,weekly,monthly,semester,six_months,annual',
            'building_id' => 'nullable|exists:buildings,id',
            'floor_id' => 'nullable|exists:floors,id',
            'gender_accommodation' => 'required|in:any,male,female',
            'furnished_status' => 'required|in:unfurnished,semi_furnished,fully_furnished',
            'bathroom_type' => 'required|in:private,shared',
            'kitchen_type' => 'required|in:private,shared,none',
        ]);

        Room::create([
            'property_id' => $property->id,
            'building_id' => $request->building_id,
            'floor_id' => $request->floor_id,
            'room_number' => $request->room_number,
            'room_type_id' => $request->room_type_id,
            'capacity' => $request->capacity,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'gender_accommodation' => $request->gender_accommodation,
            'furnished_status' => $request->furnished_status,
            'bathroom_type' => $request->bathroom_type,
            'kitchen_type' => $request->kitchen_type,
            'status' => 'active',
        ]);

        return back()->with('success', 'Room added successfully.');
    }

    public function storeBed(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|in:daily,weekly,monthly,semester,six_months,annual',
        ]);

        Bed::create([
            'room_id' => $request->room_id,
            'bed_number' => $request->bed_number,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'status' => 'available',
        ]);

        return back()->with('success', 'Bed added successfully.');
    }

    public function staff($id)
    {
        $property = Property::with(['managers'])->findOrFail($id);
        $this->checkAuthorization($property);

        return view('owner.properties.dashboard.staff', compact('property'));
    }

    public function storeStaff(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:manager,receptionist,agent,caretaker,accountant',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // Enforce the user role inside the property_user role pivot mapping
        // We will assign them and synchronize it in property_user pivot table
        $property->managers()->syncWithoutDetaching([$user->id => ['role' => $request->role]]);

        // Log action
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'property_staff_added',
            'description' => "Staff member '{$user->name}' was assigned to property '{$property->property_name}' as '{$request->role}'.",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Staff member assigned to property successfully.');
    }

    public function removeStaff(Request $request, $id, $userId)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $property->managers()->detach($userId);

        return back()->with('success', 'Staff member removed from property successfully.');
    }
}
