<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\AssetResource;
use App\Models\Asset;
use App\Models\Property;
use App\Models\AssetHandoverRecord;
use App\Models\AssetInspection;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetController extends ApiController
{
    /**
     * List assets for a property.
     */
    public function index(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        $assets = Asset::with(['category', 'room', 'bed'])
            ->where('property_id', $property->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 15));

        return $this->paginatedResponse($assets, AssetResource::class, 'Assets retrieved.');
    }

    /**
     * Add an asset to a property.
     */
    public function store(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'asset_name' => 'required|string|max:255',
            'building_id' => 'nullable|exists:buildings,id',
            'floor_id' => 'nullable|exists:floors,id',
            'room_id' => 'nullable|exists:rooms,id',
            'bed_id' => 'nullable|exists:beds,id',
            'serial_number' => 'nullable|string|max:100',
            'condition' => 'required|in:new,excellent,good,fair,damaged,needs_repair,missing,disposed',
            'purchase_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $assetCode = 'AST-' . date('Y') . '-' . strtoupper(uniqid());

        $asset = Asset::create(array_merge(
            $request->only([
                'category_id', 'asset_name', 'building_id', 'floor_id',
                'room_id', 'bed_id', 'serial_number', 'condition',
                'purchase_cost', 'notes'
            ]),
            [
                'property_id' => $property->id,
                'asset_code' => $assetCode,
                'status' => $request->filled('room_id') ? 'assigned' : 'available',
                'owner_id' => $request->user()->id,
            ]
        ));

        return $this->successResponse(
            new AssetResource($asset->load(['category', 'room', 'bed'])),
            'Asset added successfully.',
            201
        );
    }

    /**
     * Log an asset handover to tenant.
     */
    public function handover(Request $request, $propertyId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($propertyId);

        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'tenant_id' => 'required|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'condition_at_handover' => 'required|in:new,excellent,good,fair,damaged',
            'handover_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $asset = Asset::where('property_id', $property->id)->findOrFail($request->input('asset_id'));

        $handover = AssetHandoverRecord::create([
            'tenant_id' => $request->input('tenant_id'),
            'property_id' => $property->id,
            'room_id' => $request->input('room_id'),
            'asset_id' => $asset->id,
            'condition_at_handover' => $request->input('condition_at_handover'),
            'handover_date' => $request->input('handover_date'),
            'staff_id' => $request->user()->id,
            'tenant_confirmed' => false,
            'notes' => $request->input('notes'),
        ]);

        $asset->update([
            'status' => 'assigned',
            'condition' => $request->input('condition_at_handover'),
        ]);

        return $this->successResponse($handover, 'Asset handover recorded and awaiting tenant confirmation.', 201);
    }
}
