<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Institution;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::where('owner_id', auth()->id())
            ->with(['propertyType', 'region', 'district', 'ward', 'rooms.beds', 'primaryImage'])
            ->latest()
            ->get();

        return view('owner.properties.index', compact('properties'));
    }

    public function create()
    {
        return redirect()->route('owner.properties.wizard');
    }

    public function store(Request $request, PropertyService $propertyService)
    {
        $data = $request->validate([
            'property_name' => ['required', 'string', 'max:255'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'description' => ['required', 'string'],
            'region_id' => ['required', 'exists:regions,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'street' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'nearby_institution_id' => ['nullable', 'exists:institutions,id'],
            'distance_from_institution' => ['nullable', 'numeric', 'min:0'],
            'rent_structure' => ['required', 'in:per_bed,per_room,per_unit,entire_property'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'billing_period' => ['nullable', 'in:daily,weekly,monthly,semester,six_months,annual'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $data['owner_id'] = auth()->id();
        $data['status'] = 'pending_review';
        $data['verification_status'] = 'pending';

        $amenities = $request->input('amenities', []);
        
        $propertyData = collect($data)->except(['images'])->toArray();

        $property = $propertyService->createProperty($propertyData, $amenities);

        if ($request->hasFile('images')) {
            $propertyService->uploadImages($property, $request->file('images'));
        }

        return redirect()->route('owner.properties')->with('success', 'Property listing created successfully. Our compliance team will physically verify it soon.');
    }

    public function edit($id)
    {
        $property = Property::where('owner_id', auth()->id())
            ->with(['images', 'amenities'])
            ->findOrFail($id);

        $propertyTypes = PropertyType::all();
        $regions = Region::all();
        $districts = District::all();
        $wards = Ward::all();
        $institutions = Institution::all();

        return view('owner.properties.edit', compact('property', 'propertyTypes', 'regions', 'districts', 'wards', 'institutions'));
    }

    public function update(Request $request, $id, PropertyService $propertyService)
    {
        $property = Property::where('owner_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'property_name' => ['required', 'string', 'max:255'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'description' => ['required', 'string'],
            'region_id' => ['required', 'exists:regions,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'street' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'nearby_institution_id' => ['nullable', 'exists:institutions,id'],
            'distance_from_institution' => ['nullable', 'numeric', 'min:0'],
            'rent_structure' => ['required', 'in:per_bed,per_room,per_unit,entire_property'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'billing_period' => ['nullable', 'in:daily,weekly,monthly,semester,six_months,annual'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $data['status'] = 'pending_review';
        $data['verification_status'] = 'pending';

        $amenities = $request->input('amenities', []);

        $propertyData = collect($data)->except(['images'])->toArray();

        $propertyService->updateProperty($property, $propertyData, $amenities);

        if ($request->hasFile('images')) {
            $propertyService->uploadImages($property, $request->file('images'));
        }

        return redirect()->route('owner.properties')->with('success', 'Property listing updated successfully. It will be reviewed by our compliance team.');
    }

    public function deleteImage($imageId, PropertyService $propertyService)
    {
        $image = \App\Models\PropertyImage::findOrFail($imageId);

        // Verify the user owns this property
        $property = Property::where('owner_id', auth()->id())->findOrFail($image->property_id);

        $propertyService->deleteImage($imageId);

        return back()->with('success', 'Image deleted successfully.');
    }
}
