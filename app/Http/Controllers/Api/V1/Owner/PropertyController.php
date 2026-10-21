<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PropertyResource;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyController extends ApiController
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * List properties owned by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $properties = Property::with(['primaryImage', 'propertyType', 'region', 'district'])
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 10));

        return $this->paginatedResponse($properties, PropertyResource::class, 'Properties retrieved.');
    }

    /**
     * Create a new property.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_name' => 'required|string|max:255',
            'property_type_id' => 'required|exists:property_types,id',
            'description' => 'required|string',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'ward_id' => 'required|exists:wards,id',
            'street' => 'nullable|string|max:255',
            'address' => 'required|string',
            'rent_structure' => 'required|in:per_bed,per_room,per_unit,entire_property,per_person,mixed',
            'price' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|in:daily,weekly,monthly,semester,six_months,annual',
            'nearby_institution_id' => 'nullable|exists:institutions,id',
            'distance_from_institution' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $validated['owner_id'] = $request->user()->id;
        $validated['status'] = 'draft';
        $validated['verification_status'] = 'pending';

        $property = $this->propertyService->createProperty(
            $validated,
            $request->input('amenities', [])
        );

        // Upload images if included
        if ($request->hasFile('images')) {
            $this->propertyService->uploadImages($property, $request->file('images'));
        }

        return $this->successResponse(
            new PropertyResource($property->load(['primaryImage', 'propertyType', 'amenities', 'images'])),
            'Property created successfully.',
            201
        );
    }

    /**
     * Show property management details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $property = Property::with([
            'propertyType',
            'region',
            'district',
            'ward',
            'institution',
            'amenities',
            'images',
            'buildings.floors.rooms.beds',
            'rooms.beds',
            'managers'
        ])
        ->where('owner_id', $request->user()->id)
        ->findOrFail($id);

        return $this->successResponse(new PropertyResource($property), 'Property details retrieved.');
    }

    /**
     * Update property details.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'property_name' => 'required|string|max:255',
            'property_type_id' => 'required|exists:property_types,id',
            'description' => 'required|string',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'ward_id' => 'required|exists:wards,id',
            'street' => 'nullable|string|max:255',
            'address' => 'required|string',
            'rent_structure' => 'required|in:per_bed,per_room,per_unit,entire_property,per_person,mixed',
            'price' => 'nullable|numeric|min:0',
            'billing_period' => 'nullable|in:daily,weekly,monthly,semester,six_months,annual',
            'nearby_institution_id' => 'nullable|exists:institutions,id',
            'distance_from_institution' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,pending_review,published,suspended,archived',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $updated = $this->propertyService->updateProperty(
            $property,
            $validated,
            $request->input('amenities', [])
        );

        return $this->successResponse(
            new PropertyResource($updated->load(['primaryImage', 'propertyType', 'amenities', 'images'])),
            'Property updated successfully.'
        );
    }

    /**
     * Upload additional images for a property.
     */
    public function uploadImages(Request $request, $id): JsonResponse
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'room_id' => 'nullable|exists:rooms,id',
            'building_id' => 'nullable|exists:buildings,id',
        ]);

        $property = Property::where('owner_id', $request->user()->id)->findOrFail($id);

        $savedImages = $this->propertyService->uploadImages(
            $property,
            $request->file('images'),
            $request->input('room_id'),
            $request->input('building_id')
        );

        return $this->successResponse($savedImages, 'Images uploaded successfully.');
    }

    /**
     * Set primary image.
     */
    public function setPrimaryImage(Request $request, $id, $imageId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($id);
        $this->propertyService->setPrimaryImage($property, (int) $imageId);

        return $this->successResponse(null, 'Primary image updated.');
    }

    /**
     * Delete an image.
     */
    public function deleteImage(Request $request, $id, $imageId): JsonResponse
    {
        $property = Property::where('owner_id', $request->user()->id)->findOrFail($id);
        $this->propertyService->deleteImage((int) $imageId);

        return $this->successResponse(null, 'Image deleted successfully.');
    }
}
