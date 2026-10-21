<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\TenantAsset;
use App\Models\AssetHandoverRecord;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TenantAssetController extends ApiController
{
    /**
     * List assigned property assets (handovers) and registered personal assets.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->id;

        $handovers = AssetHandoverRecord::with(['asset.category', 'property', 'room'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'asset_name' => $h->asset->asset_name,
                    'asset_code' => $h->asset->asset_code,
                    'category' => $h->asset->category?->name,
                    'property_name' => $h->property->property_name,
                    'room_number' => $h->room?->room_number,
                    'condition_at_handover' => $h->condition_at_handover,
                    'handover_date' => $h->handover_date,
                    'tenant_confirmed' => (bool) $h->tenant_confirmed,
                    'tenant_confirmed_at' => $h->tenant_confirmed_at?->toIso8601String(),
                ];
            });

        $personalAssets = TenantAsset::with('category')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'asset_name' => $a->asset_name,
                    'category' => $a->category?->name,
                    'serial_number' => $a->serial_number,
                    'condition' => $a->condition,
                    'description' => $a->description,
                ];
            });

        return $this->successResponse([
            'property_handovers' => $handovers,
            'personal_assets' => $personalAssets,
        ], 'Assets retrieved successfully.');
    }

    /**
     * Tenant confirms handover condition for a property asset.
     */
    public function confirmHandover(Request $request, $handoverId): JsonResponse
    {
        $handover = AssetHandoverRecord::where('tenant_id', $request->user()->id)
            ->findOrFail($handoverId);

        $handover->update([
            'tenant_confirmed' => true,
            'tenant_confirmed_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'asset_handover_confirmed',
            'model_type' => AssetHandoverRecord::class,
            'model_id' => $handover->id,
            'description' => "Tenant confirmed receipt & condition for asset {$handover->asset->asset_name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->successResponse(null, 'Asset handover condition confirmed successfully.');
    }

    /**
     * Register a personal asset (e.g. laptop, fridge, fan).
     */
    public function storePersonal(Request $request): JsonResponse
    {
        $request->validate([
            'asset_name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|string|max:100',
            'condition' => 'required|in:new,excellent,good,fair,damaged',
            'description' => 'nullable|string|max:500',
        ]);

        $asset = TenantAsset::create([
            'tenant_id' => $request->user()->id,
            'category_id' => $request->input('category_id'),
            'asset_name' => $request->input('asset_name'),
            'serial_number' => $request->input('serial_number'),
            'condition' => $request->input('condition'),
            'description' => $request->input('description'),
        ]);

        return $this->successResponse($asset, 'Personal asset registered successfully.', 201);
    }

    /**
     * Delete a registered personal asset.
     */
    public function destroyPersonal(Request $request, $id): JsonResponse
    {
        $asset = TenantAsset::where('tenant_id', $request->user()->id)->findOrFail($id);
        $asset->delete();

        return $this->successResponse(null, 'Personal asset removed successfully.');
    }
}
