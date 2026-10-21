<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Bed;
use App\Models\PropertyType;
use App\Models\RoomType;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Institution;
use App\Models\WizardDraft;
use App\Models\Amenity;
use App\Models\PropertyImage;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyWizardController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // Get draft or initialize
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $user->id, 'wizard_type' => 'property_registration'],
            ['current_step' => 1, 'data' => []]
        );

        $propertyTypes = PropertyType::all();
        $roomTypes = RoomType::all();
        $amenities = Amenity::all();
        $regions = Region::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $wards = Ward::orderBy('name')->get();
        $institutions = Institution::all();
        $categories = \App\Models\AssetCategory::all();

        return view('owner.properties.wizard', compact(
            'draft', 
            'propertyTypes', 
            'roomTypes', 
            'amenities',
            'regions', 
            'districts', 
            'wards', 
            'institutions',
            'categories'
        ));
    }

    public function saveDraft(Request $request)
    {
        $user = auth()->user();
        $draft = WizardDraft::firstOrCreate(
            ['user_id' => $user->id, 'wizard_type' => 'property_registration'],
            ['current_step' => 1, 'data' => []]
        );

        $action = $request->input('action'); // 'next', 'previous', 'save_draft'
        $currentStep = (int) $request->input('current_step', 1);

        // Merge inputs
        $existingData = $draft->data ?? [];
        $newData = $request->except(['_token', 'action', 'current_step', 'images']);
        
        // Handle nested lists carefully
        if ($currentStep === 3) {
            // Overwrite buildings structure entirely if provided
            $existingData['buildings'] = $request->input('buildings', []);
            $existingData['rooms_count'] = $request->input('rooms_count');
            $existingData['has_multiple_floors'] = $request->input('has_multiple_floors');
        } elseif ($currentStep === 5) {
            // Overwrite rooms structure entirely if provided
            $existingData['rooms'] = $request->input('rooms', []);
        } elseif ($currentStep === 6) {
            // Overwrite assets structure entirely if provided
            $existingData['assets'] = $request->input('assets', []);
        }

        $mergedData = array_merge($existingData, $newData);

        // Handle uploaded images if any are uploaded temporarily
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('properties/temp', 'public');
                $paths[] = $path;
            }
            $mergedData['temp_images'] = array_merge($existingData['temp_images'] ?? [], $paths);
        }

        $draft->data = $mergedData;

        // Perform step validation and progression
        if ($action === 'next') {
            if ($currentStep === 1) {
                $request->validate([
                    'property_type_id' => 'required|exists:property_types,id',
                ]);
                $draft->current_step = 2;
            } elseif ($currentStep === 2) {
                $request->validate([
                    'property_name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'region_id' => 'required|exists:regions,id',
                    'district_id' => 'required|exists:districts,id',
                    'ward_id' => 'required|exists:wards,id',
                    'address' => 'required|string',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
                ]);
                $draft->current_step = 3;
            } elseif ($currentStep === 3) {
                $propTypeSlug = PropertyType::find($mergedData['property_type_id'] ?? null)?->slug ?? '';
                
                if (in_array($propTypeSlug, ['apartment', 'bedsitter', 'studio'])) {
                    $mergedData['has_multiple_floors'] = 'no';
                    $mergedData['rooms_count'] = 1;
                } elseif (in_array($propTypeSlug, ['student-hostel', 'apartment-building'])) {
                    $mergedData['has_multiple_floors'] = 'yes';
                    $request->validate([
                        'buildings' => 'required|array',
                        'buildings.*.name' => 'required|string|max:255',
                        'buildings.*.floors' => 'required|array',
                        'buildings.*.floors.*.name' => 'required|string|max:255',
                        'buildings.*.floors.*.rooms_count' => 'required|integer|min:1',
                    ]);
                } else {
                    $mergedData['has_multiple_floors'] = 'no';
                    $request->validate([
                        'rooms_count' => 'required|integer|min:1',
                    ]);
                }

                $draft->data = $mergedData;
                $draft->current_step = 4;
            } elseif ($currentStep === 4) {
                $request->validate([
                    'rent_structure' => 'required|in:per_bed,per_room,per_unit,entire_property,per_person,mixed',
                    'price' => 'nullable|numeric|min:0',
                    'billing_period' => 'nullable|in:daily,weekly,monthly,semester,six_months,annual',
                ]);

                // Initialize/Update Rooms List for Step 5
                $firstRoomType = RoomType::first()?->id;
                $defaultRentStructure = $mergedData['rent_structure'] ?? 'per_room';
                if ($defaultRentStructure === 'mixed') {
                    $defaultRentStructure = 'per_room';
                }
                $defaultPrice = $mergedData['price'] ?? 0.00;
                $defaultBillingPeriod = $mergedData['billing_period'] ?? 'monthly';

                $existingRooms = $mergedData['rooms'] ?? [];
                $newRooms = [];

                if ($mergedData['has_multiple_floors'] === 'no') {
                    $roomsCount = (int) ($mergedData['rooms_count'] ?? 1);
                    for ($i = 0; $i < $roomsCount; $i++) {
                        $roomKey = "single_r_{$i}";
                        $found = null;
                        foreach ($existingRooms as $r) {
                            if (isset($r['_key']) && $r['_key'] === $roomKey) {
                                $found = $r;
                                break;
                            }
                        }

                        $newRooms[] = $found ?: [
                            '_key' => $roomKey,
                            'room_number' => $roomsCount === 1 ? 'Unit 1' : 'Room ' . ($i + 1),
                            'room_type_id' => $firstRoomType,
                            'capacity' => 1,
                            'rental_model' => $defaultRentStructure,
                            'beds_count' => ($defaultRentStructure === 'per_bed') ? 2 : 0,
                            'price' => $defaultPrice,
                            'billing_period' => $defaultBillingPeriod,
                            'bathroom_type' => 'shared',
                            'kitchen_type' => 'none',
                            'furnished_status' => 'unfurnished',
                            'gender_accommodation' => 'any',
                            'status' => 'active',
                            'description' => '',
                            'amenities' => [],
                            'building_index' => null,
                            'floor_index' => null,
                        ];
                    }
                } else {
                    $buildingsInput = $mergedData['buildings'] ?? [];
                    foreach ($buildingsInput as $bIndex => $bData) {
                        $buildingName = $bData['name'] ?? ('Block ' . chr(65 + $bIndex));
                        $buildingLetter = substr(preg_replace('/[^a-zA-Z]/', '', $buildingName) ?: 'B', 0, 1);
                        $buildingLetter = strtoupper($buildingLetter);

                        $floorsInput = $bData['floors'] ?? [];
                        foreach ($floorsInput as $fIndex => $fData) {
                            $floorName = $fData['name'] ?? "Floor {$fIndex}";
                            $roomsCount = (int) ($fData['rooms_count'] ?? 1);
                            $floorDigit = ($fIndex + 1);

                            for ($rIndex = 0; $rIndex < $roomsCount; $rIndex++) {
                                $roomKey = "b_{$bIndex}_f_{$fIndex}_r_{$rIndex}";
                                $roomNumVal = $rIndex + 1;
                                $roomNumValStr = str_pad($roomNumVal, 2, '0', STR_PAD_LEFT);
                                $generatedNumber = "{$buildingLetter}-{$floorDigit}{$roomNumValStr}";

                                $found = null;
                                foreach ($existingRooms as $r) {
                                    if (isset($r['_key']) && $r['_key'] === $roomKey) {
                                        $found = $r;
                                        break;
                                    }
                                }

                                $newRooms[] = $found ?: [
                                    '_key' => $roomKey,
                                    'room_number' => $generatedNumber,
                                    'room_type_id' => $firstRoomType,
                                    'capacity' => 1,
                                    'rental_model' => $defaultRentStructure,
                                    'beds_count' => ($defaultRentStructure === 'per_bed') ? 2 : 0,
                                    'price' => $defaultPrice,
                                    'billing_period' => $defaultBillingPeriod,
                                    'bathroom_type' => 'shared',
                                    'kitchen_type' => 'none',
                                    'furnished_status' => 'unfurnished',
                                    'gender_accommodation' => 'any',
                                    'status' => 'active',
                                    'description' => '',
                                    'amenities' => [],
                                    'building_index' => $bIndex,
                                    'floor_index' => $fIndex,
                                ];
                            }
                        }
                    }
                }

                // If rent structure is not mixed, force rooms to match the property rental structure
                if ($mergedData['rent_structure'] !== 'mixed') {
                    foreach ($newRooms as &$roomItem) {
                        $roomItem['rental_model'] = $mergedData['rent_structure'];
                        if ($mergedData['rent_structure'] === 'per_bed' && empty($roomItem['beds_count'])) {
                            $roomItem['beds_count'] = 2;
                        }
                    }
                }

                $mergedData['rooms'] = $newRooms;
                $draft->data = $mergedData;
                $draft->current_step = 5;
            } elseif ($currentStep === 5) {
                $request->validate([
                    'rooms' => 'required|array',
                    'rooms.*.room_number' => 'required|string|max:50',
                    'rooms.*.room_type_id' => 'required|exists:room_types,id',
                    'rooms.*.capacity' => 'required|integer|min:1',
                    'rooms.*.rental_model' => 'required|in:per_bed,per_room,per_unit,entire_property,per_person',
                    'rooms.*.beds_count' => 'required_if:rooms.*.rental_model,per_bed|nullable|integer|min:0',
                    'rooms.*.price' => 'required|numeric|min:0',
                    'rooms.*.billing_period' => 'required|in:daily,weekly,monthly,semester,six_months,annual',
                    'rooms.*.bathroom_type' => 'required|in:private,shared',
                    'rooms.*.kitchen_type' => 'required|in:private,shared,none',
                    'rooms.*.furnished_status' => 'required|in:unfurnished,semi_furnished,fully_furnished',
                    'rooms.*.gender_accommodation' => 'required|in:any,male,female',
                    'rooms.*.status' => 'required|in:active,maintenance,inactive',
                ]);
                $draft->current_step = 6;
            } elseif ($currentStep === 6) {
                $request->validate([
                    'assets' => 'nullable|array',
                    'assets.*.category_id' => 'required|exists:asset_categories,id',
                    'assets.*.asset_name' => 'required|string|max:255',
                    'assets.*.quantity' => 'required|integer|min:1',
                    'assets.*.condition' => 'required|in:new,excellent,good,fair,damaged',
                    'assets.*.room_key' => 'nullable|string',
                ]);
                $mergedData['assets'] = $request->input('assets', []);
                $draft->data = $mergedData;
                $draft->current_step = 7;
            } elseif ($currentStep === 7) {
                $draft->current_step = 8;
            }
        } elseif ($action === 'previous') {
            if ($currentStep > 1) {
                $draft->current_step = $currentStep - 1;
            }
        }

        $draft->save();

        if ($action === 'save_draft') {
            return redirect()->route('owner.properties')->with('success', 'Property listing draft saved successfully.');
        }

        return redirect()->route('owner.properties.wizard');
    }

    public function submit(Request $request, PropertyService $propertyService)
    {
        $user = auth()->user();
        $draft = WizardDraft::where('user_id', $user->id)
            ->where('wizard_type', 'property_registration')
            ->firstOrFail();

        $data = array_merge($draft->data ?? [], $request->all());

        // Ensure default room exists if rooms array is empty (e.g., entire_property or single-unit listing)
        if (empty($data['rooms'])) {
            $firstRoomType = RoomType::first()?->id ?? 1;
            $data['rooms'] = [
                [
                    '_key' => 'single_r_0',
                    'room_number' => 'Entire Unit',
                    'room_type_id' => $firstRoomType,
                    'rental_model' => $data['rent_structure'] ?? 'entire_property',
                    'capacity' => 1,
                    'occupancy' => 0,
                    'price' => $data['price'] ?? 0.00,
                    'billing_period' => $data['billing_period'] ?? 'monthly',
                    'status' => 'active',
                ]
            ];
        }
        $validated = validator($data, [
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
            'has_multiple_floors' => 'required|in:yes,no',
            'rooms' => 'required|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ])->validate();

        // Create property and nested structures inside transaction
        $property = DB::transaction(function () use ($user, $validated, $data, $draft, $request, $propertyService) {
            $validated['owner_id'] = $user->id;
            $validated['status'] = 'draft';
            $validated['verification_status'] = 'pending';

            $property = Property::create($validated);

            $buildingsMap = [];
            $floorsMap = [];

            // 1. Create Building & Floor Structures
            if ($validated['has_multiple_floors'] === 'yes') {
                foreach ($data['buildings'] as $bIndex => $bData) {
                    $building = Building::create([
                        'property_id' => $property->id,
                        'building_name' => $bData['name'],
                        'building_code' => 'BLDG-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $bData['name']) ?: 'BLD', 0, 3)) . '-' . ($bIndex + 1),
                        'number_of_floors' => count($bData['floors']),
                        'status' => 'active',
                    ]);

                    $buildingsMap[$bIndex] = $building;

                    foreach ($bData['floors'] as $fIndex => $fData) {
                        $floorName = $fData['name'] ?? "Floor {$fIndex}";
                        $floor = Floor::create([
                            'building_id' => $building->id,
                            'floor_number_name' => $floorName,
                            'status' => 'active',
                        ]);
                        $floorsMap["{$bIndex}_{$fIndex}"] = $floor;
                    }
                }
            } else {
                $building = Building::create([
                    'property_id' => $property->id,
                    'building_name' => 'Main House',
                    'building_code' => 'MAIN',
                    'number_of_floors' => 1,
                    'status' => 'active',
                ]);

                $floor = Floor::create([
                    'building_id' => $building->id,
                    'floor_number_name' => 'Ground Floor',
                    'status' => 'active',
                ]);

                $buildingsMap[0] = $building;
                $floorsMap["0_0"] = $floor;
            }

            // 2. Create Rooms and Beds
            $roomsMap = [];
            foreach ($data['rooms'] as $roomData) {
                $bIdx = $roomData['building_index'] ?? null;
                $fIdx = $roomData['floor_index'] ?? null;

                $buildingId = null;
                $floorId = null;

                if ($validated['has_multiple_floors'] === 'yes') {
                    if ($bIdx !== null && isset($buildingsMap[$bIdx])) {
                        $buildingId = $buildingsMap[$bIdx]->id;
                    }
                    if ($bIdx !== null && $fIdx !== null && isset($floorsMap["{$bIdx}_{$fIdx}"])) {
                        $floorId = $floorsMap["{$bIdx}_{$fIdx}"]->id;
                    }
                } else {
                    $buildingId = $buildingsMap[0]->id;
                    $floorId = $floorsMap["0_0"]->id;
                }

                $newRoom = Room::create([
                    'property_id' => $property->id,
                    'building_id' => $buildingId,
                    'floor_id' => $floorId,
                    'room_number' => $roomData['room_number'],
                    'room_type_id' => $roomData['room_type_id'],
                    'rental_model' => $roomData['rental_model'] ?? 'per_room',
                    'capacity' => $roomData['capacity'],
                    'occupancy' => 0,
                    'description' => $roomData['description'] ?? '',
                    'gender_accommodation' => $roomData['gender_accommodation'] ?? 'any',
                    'furnished_status' => $roomData['furnished_status'] ?? 'unfurnished',
                    'bathroom_type' => $roomData['bathroom_type'] ?? 'shared',
                    'kitchen_type' => $roomData['kitchen_type'] ?? 'none',
                    'price' => $roomData['price'] ?? 0.00,
                    'billing_period' => $roomData['billing_period'] ?? 'monthly',
                    'status' => $roomData['status'] ?? 'active',
                ]);

                $roomsMap[$roomData['_key']] = $newRoom->id;

                // Attach room amenities if provided
                if (!empty($roomData['amenities'])) {
                    $newRoom->amenities()->sync($roomData['amenities']);
                }

                // Create Beds if Rental Model is Per Bed
                if ($roomData['rental_model'] === 'per_bed') {
                    $bedsCount = (int) ($roomData['beds_count'] ?? 1);
                    for ($b = 1; $b <= $bedsCount; $b++) {
                        Bed::create([
                            'room_id' => $newRoom->id,
                            'bed_number' => $newRoom->room_number . '-' . str_pad($b, 2, '0', STR_PAD_LEFT),
                            'price' => $roomData['price'] ?? 0.00,
                            'billing_period' => $roomData['billing_period'] ?? 'monthly',
                            'status' => 'available',
                        ]);
                    }
                }
            }

            // 2b. Create Property Assets registered in Step 6
            if (!empty($data['assets'])) {
                foreach ($data['assets'] as $assetData) {
                    $qty = (int) ($assetData['quantity'] ?? 1);
                    $roomKey = $assetData['room_key'] ?? null;
                    $roomId = null;

                    if ($roomKey && isset($roomsMap[$roomKey])) {
                        $roomId = $roomsMap[$roomKey];
                    }

                    $buildingId = null;
                    $floorId = null;
                    if ($roomId) {
                        $rmModel = Room::find($roomId);
                        if ($rmModel) {
                            $buildingId = $rmModel->building_id;
                            $floorId = $rmModel->floor_id;
                        }
                    }

                    for ($q = 1; $q <= $qty; $q++) {
                        Asset::create([
                            'property_id' => $property->id,
                            'category_id' => $assetData['category_id'],
                            'room_id' => $roomId,
                            'building_id' => $buildingId,
                            'floor_id' => $floorId,
                            'asset_name' => $assetData['asset_name'],
                            'asset_code' => 'AST-' . strtoupper(uniqid() . $q),
                            'condition' => $assetData['condition'] ?? 'good',
                            'status' => $roomId ? 'assigned' : 'available',
                            'owner_id' => $user->id,
                        ]);
                    }
                }
            }

            // 3. Move temporary images to permanent storage
            if (!empty($data['temp_images'])) {
                foreach ($data['temp_images'] as $tempPath) {
                    $newPath = 'properties/' . basename($tempPath);
                    if (Storage::disk('public')->exists($tempPath)) {
                        Storage::disk('public')->move($tempPath, $newPath);
                        PropertyImage::create([
                            'property_id' => $property->id,
                            'file_path' => $newPath,
                            'is_primary' => !PropertyImage::where('property_id', $property->id)->where('is_primary', true)->exists(),
                        ]);
                    }
                }
            }

            // 3b. Directly save any images uploaded during final submission
            if ($request->hasFile('images')) {
                $propertyService->uploadImages($property, $request->file('images'));
            }

            // 4. Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'property_created',
                'model_type' => Property::class,
                'model_id' => $property->id,
                'description' => "Property '{$property->property_name}' was created via wizard with its complete building, room, and bed layout.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Clear draft
            $draft->delete();

            return $property;
        });

        return redirect()->route('owner.properties')->with('success', 'Property listed successfully as draft. Property Structure, Rooms, and Beds set up complete! Submitted for Admin Verification.');
    }
}
