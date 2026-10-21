<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AssetHandoverRecord;
use App\Models\TenantAsset;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantAssetController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get handovers that this tenant belongs to
        $handovers = AssetHandoverRecord::with(['asset.property'])
            ->where('tenant_id', $user->id)
            ->latest()
            ->get();

        // Get personal assets registered by tenant
        $personalAssets = TenantAsset::where('tenant_id', $user->id)
            ->latest()
            ->get();

        return view('tenant.assets.index', compact('handovers', 'personalAssets'));
    }

    public function confirm(Request $request, $handoverId)
    {
        $user = auth()->user();
        
        $handover = AssetHandoverRecord::where('id', $handoverId)
            ->where('tenant_id', $user->id)
            ->firstOrFail();

        $handover->update([
            'tenant_confirmed' => true,
            'tenant_confirmed_at' => now(),
            'notes' => ($handover->notes ? $handover->notes . ' | ' : '') . 'Receipt confirmed by tenant.',
        ]);

        return back()->with('success', 'Asset handover receipt confirmed successfully!');
    }

    public function storePersonal(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'condition' => 'required|in:new,excellent,good,fair,damaged',
            'photo' => 'nullable|image|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        // Find tenant's current active room
        $activeContract = Contract::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->first();

        $roomId = $activeContract ? $activeContract->room_id : null;

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('tenant_assets', 'public');
        }

        TenantAsset::create([
            'tenant_id' => $user->id,
            'asset_name' => $request->input('asset_name'),
            'category' => $request->input('category'),
            'serial_number' => $request->input('serial_number'),
            'description' => $request->input('description'),
            'condition' => $request->input('condition'),
            'photo_path' => $photoPath,
            'registered_date' => now()->toDateString(),
            'notes' => $request->input('notes'),
            'room_id' => $roomId,
        ]);

        return back()->with('success', 'Personal asset registered successfully!');
    }

    public function destroyPersonal($id)
    {
        $user = auth()->user();
        $asset = TenantAsset::where('id', $id)
            ->where('tenant_id', $user->id)
            ->firstOrFail();

        if ($asset->photo_path) {
            Storage::disk('public')->delete($asset->photo_path);
        }

        $asset->delete();

        return back()->with('success', 'Personal asset removed successfully.');
    }
}
