<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PropertyResource;
use App\Models\Property;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class PropertyController extends ApiController
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Search and list published properties.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::with([
            'primaryImage',
            'propertyType',
            'region',
            'district',
            'ward',
            'institution',
            'amenities'
        ])
        ->published()
        ->verified();

        // 1. Keyword search
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('property_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 2. Location filtering
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }
        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->input('ward_id'));
        }

        // 3. Property Type
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->input('property_type_id'));
        }

        // 4. Rent Structure
        if ($request->filled('rent_structure')) {
            $query->where('rent_structure', $request->input('rent_structure'));
        }

        // 5. University / Institution & Distance
        if ($request->filled('institution_id')) {
            $query->where('nearby_institution_id', $request->input('institution_id'));
            if ($request->filled('max_distance')) {
                $query->where('distance_from_institution', '<=', $request->input('max_distance'));
            }
        }

        // 6. Price Range
        if ($request->filled('min_price')) {
            $min = (float) $request->input('min_price');
            $query->where(function ($q) use ($min) {
                $q->where('price', '>=', $min)
                  ->orWhereHas('rooms', fn($sq) => $sq->where('price', '>=', $min))
                  ->orWhereHas('beds', fn($sq) => $sq->where('price', '>=', $min));
            });
        }
        if ($request->filled('max_price')) {
            $max = (float) $request->input('max_price');
            $query->where(function ($q) use ($max) {
                $q->where('price', '<=', $max)
                  ->orWhereHas('rooms', fn($sq) => $sq->where('price', '<=', $max))
                  ->orWhereHas('beds', fn($sq) => $sq->where('price', '<=', $max));
            });
        }

        // 7. Amenities
        if ($request->filled('amenities') && is_array($request->input('amenities'))) {
            foreach ($request->input('amenities') as $amenityId) {
                $query->whereHas('amenities', fn($q) => $q->where('amenities.id', $amenityId));
            }
        }

        $perPage = (int) $request->input('per_page', 12);
        $properties = $query->latest()->paginate($perPage);

        return $this->paginatedResponse($properties, PropertyResource::class, 'Properties retrieved successfully.');
    }

    /**
     * Get featured properties.
     */
    public function featured(): JsonResponse
    {
        $featured = Cache::remember('api_featured_properties', 600, function () {
            return Property::with([
                'primaryImage',
                'propertyType',
                'region',
                'district',
                'ward',
                'institution'
            ])
            ->published()
            ->verified()
            ->featured()
            ->latest()
            ->limit(8)
            ->get();
        });

        return $this->successResponse(PropertyResource::collection($featured), 'Featured properties retrieved.');
    }

    /**
     * Get single property details with availability summary and full layout.
     */
    public function show($id): JsonResponse
    {
        $property = Cache::remember("api_property_details_{$id}", 300, function () use ($id) {
            return Property::with([
                'owner',
                'propertyType',
                'region',
                'district',
                'ward',
                'institution',
                'amenities',
                'images',
                'buildings.floors.rooms.beds',
                'rooms.beds',
                'reviews.tenant'
            ])
            ->published()
            ->findOrFail($id);
        });

        $property->availability_summary = $this->availabilityService->getAvailabilitySummary($property);

        return $this->successResponse(new PropertyResource($property), 'Property details retrieved.');
    }

    /**
     * Real-time availability check for a specific room or bed.
     */
    public function checkAvailability(Request $request, $id): JsonResponse
    {
        $property = Property::findOrFail($id);
        $roomId = $request->input('room_id');
        $bedId = $request->input('bed_id');

        $isAvailable = false;
        if ($bedId) {
            $isAvailable = $this->availabilityService->isBedAvailableForBooking((int) $bedId);
        } elseif ($roomId) {
            $isAvailable = $this->availabilityService->isRoomAvailableForBooking((int) $roomId);
        } else {
            $isAvailable = $this->availabilityService->isPropertyAvailableForBooking((int) $property->id);
        }

        return $this->successResponse([
            'property_id' => (int) $id,
            'room_id' => $roomId ? (int) $roomId : null,
            'bed_id' => $bedId ? (int) $bedId : null,
            'is_available' => $isAvailable,
            'summary' => $this->availabilityService->getAvailabilitySummary($property),
        ], 'Availability check completed.');
    }
}
