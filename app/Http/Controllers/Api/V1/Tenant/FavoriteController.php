<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PropertyResource;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends ApiController
{
    /**
     * List user's favorite properties.
     */
    public function index(Request $request): JsonResponse
    {
        $propertyIds = Favorite::where('user_id', $request->user()->id)->pluck('property_id');
        $properties = Property::with(['primaryImage', 'propertyType', 'region', 'district'])
            ->whereIn('id', $propertyIds)
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($properties, PropertyResource::class, 'Favorites retrieved.');
    }

    /**
     * Toggle favorite status on a property.
     */
    public function toggle(Request $request, $propertyId): JsonResponse
    {
        $property = Property::findOrFail($propertyId);
        $userId = $request->user()->id;

        $favorite = Favorite::where('user_id', $userId)->where('property_id', $property->id)->first();

        if ($favorite) {
            $favorite->delete();
            return $this->successResponse(['is_favorite' => false], 'Property removed from favorites.');
        } else {
            Favorite::create([
                'user_id' => $userId,
                'property_id' => $property->id,
            ]);
            return $this->successResponse(['is_favorite' => true], 'Property saved to favorites.');
        }
    }
}
