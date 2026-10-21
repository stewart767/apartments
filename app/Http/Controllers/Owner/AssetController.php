<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetHandoverRecord;
use App\Models\AssetInspection;
use App\Models\Room;
use App\Models\Bed;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    protected function checkAuthorization(Property $property)
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return;
        }

        if ($property->owner_id === $user->id) {
            return;
        }

        $isAssigned = DB::table('property_user')
            ->where('property_id', $property->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'You are not authorized to manage assets for this property.');
        }
    }

    public function index($id)
    {
        $property = Property::with(['rooms.beds'])->findOrFail($id);
        $this->checkAuthorization($property);

        $assets = Asset::with(['category', 'room', 'bed', 'handoverRecords.tenant'])
            ->where('property_id', $property->id)
            ->get();

        $categories = AssetCategory::all();
        
        // Find tenants in this property to assign handovers
        $tenants = User::whereHas('contracts', function($q) use ($property) {
            $q->where('property_id', $property->id)->where('status', 'active');
        })->get();

        return view('owner.properties.dashboard.assets', compact('property', 'assets', 'categories', 'tenants'));
    }

    public function store(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'required|in:new,excellent,good,fair,damaged',
            'room_id' => 'nullable|exists:rooms,id',
            'bed_id' => 'nullable|exists:beds,id',
        ]);

        Asset::create([
            'property_id' => $property->id,
            'category_id' => $request->category_id,
            'room_id' => $request->room_id,
            'bed_id' => $request->bed_id,
            'asset_name' => $request->asset_name,
            'asset_code' => 'AST-' . strtoupper(uniqid()),
            'serial_number' => $request->serial_number,
            'condition' => $request->condition,
            'status' => $request->room_id ? 'assigned' : 'available',
        ]);

        return back()->with('success', 'Asset added successfully.');
    }

    public function handover(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'tenant_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        AssetHandoverRecord::create([
            'property_id' => $property->id,
            'room_id' => $asset->room_id,
            'bed_id' => $asset->bed_id,
            'asset_id' => $asset->id,
            'tenant_id' => $request->tenant_id,
            'handover_date' => now(),
            'condition_at_handover' => in_array($asset->condition, ['new', 'excellent', 'good', 'fair', 'damaged']) ? $asset->condition : 'good',
            'staff_id' => auth()->id(),
            'tenant_confirmed' => false,
            'notes' => $request->notes,
        ]);

        $asset->update(['status' => 'assigned']);

        return back()->with('success', 'Asset handover record created. Tenant needs to confirm receipt.');
    }

    public function inspect(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->checkAuthorization($property);

        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'condition' => 'required|in:new,excellent,good,fair,damaged,needs_repair,missing,disposed',
            'type' => 'required|in:checkin,checkout,routine',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::findOrFail($request->asset_id);
        $oldCondition = $asset->condition;
        $newCondition = $request->condition;

        // Get the active handover record
        $activeHandover = AssetHandoverRecord::where('asset_id', $asset->id)
            ->latest()
            ->first();

        // Create Inspection
        AssetInspection::create([
            'asset_handover_record_id' => $activeHandover ? $activeHandover->id : null,
            'original_condition' => $activeHandover ? $activeHandover->condition_at_handover : 'good',
            'current_condition' => in_array($newCondition, ['new', 'excellent', 'good', 'fair', 'damaged', 'needs_repair', 'missing', 'disposed']) ? $newCondition : 'good',
            'damage_charges' => $request->input('damage_charges', 0),
            'notes' => "Asset '{$asset->asset_name}' inspected. Condition changed from {$oldCondition} to {$newCondition}. Notes: " . $request->notes,
            'staff_id' => auth()->id(),
            'inspection_date' => now(),
        ]);

        $asset->update([
            'condition' => in_array($newCondition, ['new', 'excellent', 'good', 'fair', 'damaged', 'needs_repair', 'missing', 'disposed']) ? $newCondition : 'good',
            'status' => ($newCondition === 'damaged' || $newCondition === 'needs_repair') ? 'maintenance' : $asset->status,
        ]);

        return back()->with('success', 'Asset inspection logged successfully. Condition updated.');
    }
}
