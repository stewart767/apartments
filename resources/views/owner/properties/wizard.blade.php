@extends('layouts.admin')

@section('title', 'Property Listing Wizard')
@section('breadcrumb_title', 'Listing Wizard')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map { height: 300px; border-radius: 1.5rem; }
</style>
@endsection

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto" x-data="propertyWizard()">
    <!-- Wizard Progress Header -->
    <div class="border-b border-slate-200 pb-6 mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Add New Property Setup Wizard</h1>
        <p class="mt-1 text-sm text-slate-500 font-medium">
            Follow the 8 steps to register your property type, basic details, structure, leasing model, rooms/units, assets, and submit.
        </p>

        <!-- Progress Steps Bar -->
        <div class="mt-8 grid grid-cols-4 md:flex md:items-center md:justify-between gap-y-4 text-xs sm:text-sm font-semibold">
            <!-- Step 1 -->
            <div class="flex items-center" :class="step >= 1 ? 'text-indigo-650 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 1 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 1 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    1
                </span>
                <span class="ml-2">Category</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 2 ? 'border-indigo-600' : ''"></div>

            <!-- Step 2 -->
            <div class="flex items-center" :class="step >= 2 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 2 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 2 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    2
                </span>
                <span class="ml-2">Basic Info</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 3 ? 'border-indigo-600' : ''"></div>

            <!-- Step 3 -->
            <div class="flex items-center" :class="step >= 3 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 3 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 3 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    3
                </span>
                <span class="ml-2">Structure</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 4 ? 'border-indigo-600' : ''"></div>

            <!-- Step 4 -->
            <div class="flex items-center" :class="step >= 4 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 4 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 4 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    4
                </span>
                <span class="ml-2">Model</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 5 ? 'border-indigo-600' : ''"></div>

            <!-- Step 5 -->
            <div class="flex items-center" :class="step >= 5 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 5 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 5 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    5
                </span>
                <span class="ml-2">Rooms</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 6 ? 'border-indigo-600' : ''"></div>

            <!-- Step 6 -->
            <div class="flex items-center" :class="step >= 6 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 6 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 6 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    6
                </span>
                <span class="ml-2">Assets</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 7 ? 'border-indigo-600' : ''"></div>

            <!-- Step 7 -->
            <div class="flex items-center" :class="step >= 7 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 7 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : (step > 7 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500')">
                    7
                </span>
                <span class="ml-2">Summary</span>
            </div>
            <div class="hidden md:block flex-1 border-t border-slate-200 mx-2" :class="step >= 8 ? 'border-indigo-600' : ''"></div>

            <!-- Step 8 -->
            <div class="flex items-center" :class="step >= 8 ? 'text-indigo-655 font-bold' : 'text-slate-400'">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-bold transition-all text-xs"
                      :class="step == 8 ? 'bg-indigo-600 text-white ring-4 ring-indigo-50/50' : 'bg-slate-100 text-slate-500'">
                    8
                </span>
                <span class="ml-2">Photos</span>
            </div>
        </div>
    </div>

    <!-- Wizard Form Body -->
    <div class="bg-white border border-slate-100 rounded-3xl shadow-xl shadow-slate-100/50 p-8 sm:p-10">
        <form id="wizardForm" method="POST" action="{{ route('owner.properties.wizard.save-draft') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="current_step" :value="step">
            <input type="hidden" name="action" x-model="action">

            <!-- STEP 1: PROPERTY TYPE -->
            <div x-show="step == 1" x-transition.fade.duration.200ms class="space-y-6">
                <h3 class="text-lg font-bold text-slate-900">Select Property Category</h3>
                <p class="text-xs text-slate-500">Pick the property category that best matches your setup.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($propertyTypes as $type)
                        <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                               :class="propertyTypeId == {{ $type->id }} ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                            <input type="radio" name="property_type_id" value="{{ $type->id }}" x-model="propertyTypeId" class="sr-only" required>
                            <div class="flex flex-col">
                                <span class="block text-sm font-bold text-slate-900" :class="propertyTypeId == {{ $type->id }} ? 'text-indigo-900' : ''">{{ $type->name }}</span>
                                <span class="mt-1 text-xs text-slate-500">{{ $type->description ?? 'Register rooms/spaces' }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 2: BASIC INFO & GPS LOCATION -->
            <div x-show="step == 2" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <h3 class="text-lg font-bold text-slate-900">Basic Information & Address</h3>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="property_name" class="block text-sm font-semibold text-slate-700">Property / Hostel Name</label>
                        <input id="property_name" name="property_name" type="text" required value="{{ old('property_name', $draft->data['property_name'] ?? '') }}" placeholder="e.g. Mwenge Executive Hostels"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">General Description</label>
                    <textarea id="description" name="description" rows="3" required placeholder="Describe backup power, water security, common areas, rules..."
                              class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">{{ old('description', $draft->data['description'] ?? '') }}</textarea>
                </div>

                <!-- Locations Dropdowns -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
                    <div>
                        <label for="region_id" class="block text-sm font-semibold text-slate-700">Region</label>
                        <select id="region_id" name="region_id" x-model="regionId" @change="districtId = ''; wardId = ''" required
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <option value="" disabled selected>Select Region...</option>
                            <template x-for="r in regions" :key="r.id">
                                <option :value="r.id" x-text="r.name" :selected="r.id == regionId"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="district_id" class="block text-sm font-semibold text-slate-700">District</label>
                        <select id="district_id" name="district_id" x-model="districtId" @change="wardId = ''" required :disabled="!regionId"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                            <option value="" disabled selected>Select District...</option>
                            <template x-for="d in filteredDistricts" :key="d.id">
                                <option :value="d.id" x-text="d.name" :selected="d.id == districtId"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="ward_id" class="block text-sm font-semibold text-slate-700">Ward</label>
                        <select id="ward_id" name="ward_id" x-model="wardId" required :disabled="!districtId"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                            <option value="" disabled selected>Select Ward...</option>
                            <template x-for="w in filteredWards" :key="w.id">
                                <option :value="w.id" x-text="w.name" :selected="w.id == wardId"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-slate-700">Physical Address / Street / Landmark</label>
                        <input id="address" name="address" type="text" required value="{{ old('address', $draft->data['address'] ?? '') }}" placeholder="e.g. Mlalakuwa St, near Mwenge market"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>
                    <div>
                        <label for="street" class="block text-sm font-semibold text-slate-700">Street / Plot</label>
                        <input id="street" name="street" type="text" value="{{ old('street', $draft->data['street'] ?? '') }}" placeholder="e.g. Plot 24"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                    <div>
                        <label for="nearby_institution_id" class="block text-sm font-semibold text-slate-700">Nearby Academic Institution (Optional)</label>
                        <select id="nearby_institution_id" name="nearby_institution_id"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <option value="" selected>None / Select Institution...</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" {{ old('nearby_institution_id', $draft->data['nearby_institution_id'] ?? '') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="distance_from_institution" class="block text-sm font-semibold text-slate-700">Distance in KM</label>
                        <input id="distance_from_institution" name="distance_from_institution" type="number" step="0.01" min="0" value="{{ old('distance_from_institution', $draft->data['distance_from_institution'] ?? '') }}" placeholder="e.g. 0.50"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>
                </div>

                <!-- GPS Location selection -->
                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">GPS Location selection (Click on map to select coordinates)</label>
                    <div id="map" class="mb-4"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-xs font-semibold text-slate-500">Latitude</label>
                            <input id="latitude" name="latitude" type="text" readonly x-model="latitude" class="mt-1 block w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 text-xs focus:outline-none">
                        </div>
                        <div>
                            <label for="longitude" class="block text-xs font-semibold text-slate-500">Longitude</label>
                            <input id="longitude" name="longitude" type="text" readonly x-model="longitude" class="mt-1 block w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 text-xs focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: PROPERTY STRUCTURE CONFIGURATION -->
            <div x-show="step == 3" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <h3 class="text-lg font-bold text-slate-900">Define Property Structure</h3>
                <p class="text-xs text-slate-500">Specify wings, floors, and room counts of your accommodation layout.</p>

                <!-- Single Unit skips structure setup -->
                <div x-show="selectedCategoryType === 'apartment' || selectedCategoryType === 'bedsitter' || selectedCategoryType === 'studio'" class="p-6 bg-slate-50 border border-slate-200 rounded-2xl text-center text-slate-500">
                    <h4 class="text-sm font-bold text-slate-700">Single Unit Configuration</h4>
                    <p class="text-xs mt-2">This property type represents a single independent unit flat/apartment/bedsitter. No structural wing setup is required. The wizard will automatically generate 1 unit configuration for you.</p>
                </div>

                <!-- Normal House layout: room count input -->
                <div x-show="selectedCategoryType !== 'apartment' && selectedCategoryType !== 'bedsitter' && selectedCategoryType !== 'studio' && selectedCategoryType !== 'student-hostel' && selectedCategoryType !== 'apartment-building'" class="space-y-4">
                    <div>
                        <label for="rooms_count" class="block text-sm font-semibold text-slate-700">How many rooms does this property have?</label>
                        <input id="rooms_count" name="rooms_count" type="number" min="1" x-model="roomsCount"
                               class="mt-1.5 block w-40 px-4 py-2 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        <p class="mt-2 text-xs text-slate-400">The system will generate independent configuration profiles for each of these rooms.</p>
                    </div>
                </div>

                <!-- Multi-Storey / Hostel configuration editor -->
                <div x-show="selectedCategoryType === 'student-hostel' || selectedCategoryType === 'apartment-building'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-900">Configure Buildings & Floors</h4>
                        <button type="button" @click="addBuilding()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-semibold rounded-lg transition-colors">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Add Building Block</span>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <template x-for="(building, bIndex) in buildings" :key="bIndex">
                            <div class="p-6 bg-slate-50/50 border border-slate-200 rounded-2xl space-y-4 relative">
                                <button type="button" @click="removeBuilding(bIndex)" class="absolute top-4 right-4 p-1.5 text-slate-400 hover:text-rose-650 rounded-md hover:bg-white border border-slate-200 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Building Block / Wing Name</label>
                                        <input type="text" :name="'buildings['+bIndex+'][name]'" x-model="building.name" placeholder="e.g. Block A" required
                                               class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Floors under this block</span>
                                        <button type="button" @click="addFloor(bIndex)" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:underline">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>Add Floor</span>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3">
                                        <template x-for="(floor, fIndex) in building.floors" :key="fIndex">
                                            <div class="flex items-center gap-4 bg-white p-3 border border-slate-200 rounded-xl shadow-sm">
                                                <div class="flex-1">
                                                    <label class="block text-[10px] font-semibold text-slate-400">Floor Name</label>
                                                    <input type="text" :name="'buildings['+bIndex+'][floors]['+fIndex+'][name]'" x-model="floor.name" required
                                                           class="block w-full py-1 border-0 border-b border-slate-200 focus:border-indigo-600 focus:ring-0 text-xs">
                                                </div>
                                                <div class="w-28">
                                                    <label class="block text-[10px] font-semibold text-slate-400">Rooms on Floor</label>
                                                    <input type="number" :name="'buildings['+bIndex+'][floors]['+fIndex+'][rooms_count]'" min="1" x-model.number="floor.rooms_count" required
                                                           class="block w-full py-1 border-0 border-b border-slate-200 focus:border-indigo-600 focus:ring-0 text-xs text-center font-bold">
                                                </div>
                                                <button type="button" @click="removeFloor(bIndex, fIndex)" class="p-1 text-slate-400 hover:text-rose-650">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- STEP 4: RENTAL LEASING MODEL -->
            <div x-show="step == 4" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <h3 class="text-lg font-bold text-slate-900">Rental / Leasing Model</h3>
                <p class="text-xs text-slate-500">Define how the property units will be priced and leased (Mixed model allows room-level configuration).</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Entire Property -->
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                           :class="rentStructure === 'entire_property' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                        <input type="radio" name="rent_structure" value="entire_property" x-model="rentStructure" class="sr-only" required>
                        <div class="flex flex-col">
                            <span class="block text-sm font-bold text-slate-900">Entire Property</span>
                            <span class="mt-1 text-xs text-slate-500">The entire property is rented as one single unit.</span>
                        </div>
                    </label>

                    <!-- Per Unit -->
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                           :class="rentStructure === 'per_unit' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                        <input type="radio" name="rent_structure" value="per_unit" x-model="rentStructure" class="sr-only">
                        <div class="flex flex-col">
                            <span class="block text-sm font-bold text-slate-900">Per Unit / Flat</span>
                            <span class="mt-1 text-xs text-slate-500">Each apartment/unit is independently rentable.</span>
                        </div>
                    </label>

                    <!-- Per Room -->
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                           :class="rentStructure === 'per_room' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                        <input type="radio" name="rent_structure" value="per_room" x-model="rentStructure" class="sr-only">
                        <div class="flex flex-col">
                            <span class="block text-sm font-bold text-slate-900">Per Room</span>
                            <span class="mt-1 text-xs text-slate-500">Rooms are leased individually (entire room lease).</span>
                        </div>
                    </label>

                    <!-- Per Bed -->
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                           :class="rentStructure === 'per_bed' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                        <input type="radio" name="rent_structure" value="per_bed" x-model="rentStructure" class="sr-only">
                        <div class="flex flex-col">
                            <span class="block text-sm font-bold text-slate-900">Per Bed</span>
                            <span class="mt-1 text-xs text-slate-500">Beds are leased individually (shared roommates hostel).</span>
                        </div>
                    </label>

                    <!-- Per Person -->
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                           :class="rentStructure === 'per_person' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                        <input type="radio" name="rent_structure" value="per_person" x-model="rentStructure" class="sr-only">
                        <div class="flex flex-col">
                            <span class="block text-sm font-bold text-slate-900">Per Person</span>
                            <span class="mt-1 text-xs text-slate-500">Capacity-based shared rooms with individual contracts.</span>
                        </div>
                    </label>

                    <!-- Mixed -->
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 hover:bg-slate-50 transition-all shadow-sm border-slate-200"
                           :class="rentStructure === 'mixed' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : ''">
                        <input type="radio" name="rent_structure" value="mixed" x-model="rentStructure" class="sr-only">
                        <div class="flex flex-col">
                            <span class="block text-sm font-bold text-slate-900">Mixed Model</span>
                            <span class="mt-1 text-xs text-slate-500">Rooms can use different structures (customizable in next step).</span>
                        </div>
                    </label>
                </div>

                <!-- Pricing & billing details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                    <div>
                        <label for="price" class="block text-sm font-semibold text-slate-700">Base / Default Rate (TZS)</label>
                        <input id="price" name="price" type="number" required x-model="price" placeholder="e.g. 150000"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                    </div>

                    <div>
                        <label for="billing_period" class="block text-sm font-semibold text-slate-700">Default Billing Frequency</label>
                        <select id="billing_period" name="billing_period" required x-model="billingPeriod"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="semester">Semester</option>
                            <option value="six_months">6 Months</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 5: ROOMS & BEDS CONFIGURATION (Master-Detail UI) -->
            <div x-show="step == 5" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-bold text-slate-900">Configure Room Details</h3>
                    <p class="text-xs text-slate-500">Edit numbers, capacities, pricing, models, and amenities for each room space.</p>
                </div>

                <div x-show="rooms.length === 0" class="p-6 bg-slate-50 border border-slate-200 rounded-2xl text-center text-slate-500">
                    <p class="text-sm font-semibold">No rooms generated.</p>
                    <p class="text-xs mt-1">Please ensure you completed earlier structure steps to generate rooms.</p>
                </div>

                <div x-show="rooms.length > 0" class="flex flex-col lg:flex-row gap-6">
                    <!-- Left Sidebar Room List -->
                    <div class="w-full lg:w-1/3 bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col max-h-[500px]">
                        <span class="text-xs font-bold text-slate-400 uppercase mb-3 block">Rooms / Spaces List</span>
                        <div class="overflow-y-auto space-y-2 flex-1 pr-1">
                            <template x-for="(rm, idx) in rooms" :key="idx">
                                <button type="button" @click="selectedRoomIndex = idx"
                                        class="w-full text-left px-4 py-3 rounded-xl border transition-all text-xs font-semibold flex items-center justify-between"
                                        :class="selectedRoomIndex == idx ? 'bg-indigo-600 border-indigo-650 text-white shadow-md' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200'">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm leading-tight" x-text="rm.room_number"></span>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[9px] opacity-90 px-1.5 py-0.5 rounded-full font-bold uppercase"
                                                  :class="selectedRoomIndex == idx ? 'bg-indigo-700 text-indigo-100' : 'bg-slate-100 text-slate-650'"
                                                  x-text="rm.rental_model.replace('_', ' ')"></span>
                                            <span class="text-[10px] font-bold" :class="isRoomComplete(idx) ? (selectedRoomIndex == idx ? 'text-emerald-300' : 'text-emerald-600') : (selectedRoomIndex == idx ? 'text-amber-200' : 'text-amber-600')"
                                                  x-text="isRoomComplete(idx) ? '✓ Complete' : '⚠ Incomplete'"></span>
                                        </div>
                                    </div>
                                    <i data-lucide="chevron-right" class="w-4 h-4 opacity-70"></i>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Right Active Room Config Form -->
                    <div class="flex-1 bg-slate-50/30 border border-slate-200 rounded-2xl p-6 relative">
                        <template x-for="(rm, idx) in rooms" :key="idx">
                            <div x-show="selectedRoomIndex == idx" class="space-y-6">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h4 class="text-sm font-bold text-indigo-900 uppercase">Editing Room Details: <span x-text="rm.room_number"></span></h4>
                                    <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full font-bold" x-text="'Room ' + (idx + 1) + ' of ' + rooms.length"></span>
                                </div>

                                <input type="hidden" :name="'rooms['+idx+'][building_index]'" :value="rm.building_index">
                                <input type="hidden" :name="'rooms['+idx+'][floor_index]'" :value="rm.floor_index">
                                <input type="hidden" :name="'rooms['+idx+'][_key]'" :value="rm._key">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Room/Unit Number Name</label>
                                        <input type="text" :name="'rooms['+idx+'][room_number]'" x-model="rm.room_number" required
                                               class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Room Type Space</label>
                                        <select :name="'rooms['+idx+'][room_type_id]'" x-model="rm.room_type_id" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            @foreach($roomTypes as $rt)
                                                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Rental Leasing Model</label>
                                        <!-- Editable if rentStructure is mixed, otherwise locked to Step 4 choice -->
                                        <template x-if="rentStructure === 'mixed'">
                                            <select :name="'rooms['+idx+'][rental_model]'" x-model="rm.rental_model" required
                                                    class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="entire_property">Entire Property</option>
                                                <option value="per_unit">Per Unit / Flat</option>
                                                <option value="per_room">Per Room</option>
                                                <option value="per_bed">Per Bed (Hostel share)</option>
                                                <option value="per_person">Per Person</option>
                                            </select>
                                        </template>
                                        <template x-if="rentStructure !== 'mixed'">
                                            <div class="mt-1.5 block w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-700 capitalize font-semibold">
                                                <span x-text="rentStructure.replace('_', ' ')"></span>
                                                <input type="hidden" :name="'rooms['+idx+'][rental_model]'" :value="rentStructure">
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Beds input (Conditional on Per Bed room rental model) -->
                                    <div x-show="rm.rental_model === 'per_bed'" x-transition>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Number of Beds in Room</label>
                                        <input type="number" :name="'rooms['+idx+'][beds_count]'" min="1" x-model.number="rm.beds_count"
                                               class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Capacity (Max Occupants)</label>
                                        <input type="number" :name="'rooms['+idx+'][capacity]'" min="1" x-model.number="rm.capacity" required
                                               class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Price (TZS)</label>
                                        <input type="number" :name="'rooms['+idx+'][price]'" min="0" x-model="rm.price" required
                                               class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Billing Frequency</label>
                                        <select :name="'rooms['+idx+'][billing_period]'" x-model="rm.billing_period" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="semester">Semester</option>
                                            <option value="six_months">6 Months</option>
                                            <option value="annual">Annual</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Bathroom Type</label>
                                        <select :name="'rooms['+idx+'][bathroom_type]'" x-model="rm.bathroom_type" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="private">Private (Self-contained)</option>
                                            <option value="shared">Shared</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Kitchen Type</label>
                                        <select :name="'rooms['+idx+'][kitchen_type]'" x-model="rm.kitchen_type" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="private">Private Kitchen</option>
                                            <option value="shared">Shared Kitchen</option>
                                            <option value="none">No Kitchen</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Furnished Status</label>
                                        <select :name="'rooms['+idx+'][furnished_status]'" x-model="rm.furnished_status" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="unfurnished">Unfurnished</option>
                                            <option value="semi_furnished">Semi Furnished</option>
                                            <option value="fully_furnished">Fully Furnished</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Gender Accommodation Allowed</label>
                                        <select :name="'rooms['+idx+'][gender_accommodation]'" x-model="rm.gender_accommodation" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="any">Any (Mixed)</option>
                                            <option value="male">Male Only</option>
                                            <option value="female">Female Only</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase">Room Status</label>
                                        <select :name="'rooms['+idx+'][status]'" x-model="rm.status" required
                                                class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="active">Active / Available</option>
                                            <option value="maintenance">Under Maintenance</option>
                                            <option value="inactive">Inactive / Closed</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Room Amenities</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-white p-4 border border-slate-200 rounded-xl shadow-sm">
                                        @foreach($amenities as $amenity)
                                            <label class="inline-flex items-center text-xs font-medium text-slate-700 cursor-pointer">
                                                <input type="checkbox" :name="'rooms['+idx+'][amenities][]'" value="{{ $amenity->id }}"
                                                       :checked="rm.amenities && rm.amenities.includes('{{ $amenity->id }}') || rm.amenities && rm.amenities.includes({{ $amenity->id }})"
                                                       @change="toggleAmenity(idx, {{ $amenity->id }}, $event.target.checked)"
                                                       class="rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 mr-2">
                                                <span>{{ $amenity->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Room Description / Space Details</label>
                                    <textarea :name="'rooms['+idx+'][description]'" x-model="rm.description" rows="2" placeholder="Describe study desks, views..."
                                              class="mt-1.5 block w-full px-3 py-2 border border-slate-200 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- STEP 6: PROPERTY ASSETS REGISTER -->
            <div x-show="step == 6" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Property Assets Management</h3>
                        <p class="text-xs text-slate-500">Register property-owned assets (mattress, wardrobe, etc.) and assign them to rooms.</p>
                    </div>
                    <button type="button" @click="addAsset()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-semibold rounded-lg transition-colors">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Asset</span>
                    </button>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[9px] tracking-wider">
                                <th class="p-4">Category</th>
                                <th class="p-4">Asset Name</th>
                                <th class="p-4">Quantity</th>
                                <th class="p-4">Condition</th>
                                <th class="p-4">Location / Room</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(asset, aIdx) in assets" :key="aIdx">
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                    <td class="p-3">
                                        <select :name="'assets['+aIdx+'][category_id]'" x-model="asset.category_id" required
                                                class="block w-full py-1 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 text-xs bg-white">
                                            <option value="" disabled>Category...</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="'assets['+aIdx+'][asset_name]'" x-model="asset.asset_name" placeholder="e.g. Foam Mattress" required
                                               class="block w-full py-1 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 text-xs px-2 bg-white">
                                    </td>
                                    <td class="p-3 w-20">
                                        <input type="number" :name="'assets['+aIdx+'][quantity]'" x-model.number="asset.quantity" min="1" required
                                               class="block w-full py-1 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 text-xs text-center font-semibold bg-white">
                                    </td>
                                    <td class="p-3">
                                        <select :name="'assets['+aIdx+'][condition]'" x-model="asset.condition" required
                                                class="block w-full py-1 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 text-xs bg-white">
                                            <option value="new">New</option>
                                            <option value="excellent">Excellent</option>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="damaged">Damaged</option>
                                        </select>
                                    </td>
                                    <td class="p-3">
                                        <select :name="'assets['+aIdx+'][room_key]'" x-model="asset.room_key"
                                                class="block w-full py-1 border border-slate-200 rounded-lg focus:ring-1 focus:ring-indigo-500 text-xs bg-white">
                                            <option value="">Entire Property (Common)</option>
                                            <template x-for="(rm, rIdx) in rooms" :key="rIdx">
                                                <option :value="rm._key" x-text="rm.room_number"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeAsset(aIdx)" class="p-1.5 text-slate-400 hover:text-rose-650 rounded-md hover:bg-slate-100 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="assets.length === 0">
                                <td colspan="6" class="p-8 text-center text-slate-400 font-medium">
                                    No assets registered yet. Click "Add Asset" to start cataloging property inventory.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- STEP 7: STRUCTURAL SUMMARY -->
            <div x-show="step == 7" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <h3 class="text-lg font-bold text-slate-900">Review Accommodation Structure Summary</h3>
                <p class="text-xs text-slate-500">Confirm details of your building structure, rooms, and assets before completing submission.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Quick Statistics -->
                    <div class="bg-indigo-50/50 border border-indigo-100 p-6 rounded-2xl space-y-4 md:col-span-1">
                        <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-2">Structure Stats</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-xs text-slate-655 font-semibold">
                                <span>Property Category:</span>
                                <span class="font-bold text-slate-800" x-text="propertyTypeName()"></span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-655 font-semibold" x-show="hasMultipleFloors === 'yes'">
                                <span>Total Buildings:</span>
                                <span class="font-bold text-slate-800" x-text="buildings.length"></span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-655 font-semibold">
                                <span>Total Rooms / Units:</span>
                                <span class="font-bold text-slate-800" x-text="rooms.length"></span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-655 font-semibold">
                                <span>Total Assets:</span>
                                <span class="font-bold text-slate-800" x-text="assets.reduce((sum, a) => sum + (parseInt(a.quantity) || 0), 0)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Structural Hierarchy Cards -->
                    <div class="md:col-span-2 space-y-4">
                        <template x-if="hasMultipleFloors === 'yes'">
                            <div class="space-y-4">
                                <template x-for="(b, bIdx) in buildings" :key="bIdx">
                                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                                        <h5 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                            <i data-lucide="building" class="w-4 h-4 text-indigo-650"></i>
                                            <span x-text="b.name"></span>
                                        </h5>
                                        <div class="pl-6 space-y-2 border-l border-slate-100">
                                            <template x-for="(f, fIdx) in b.floors" :key="fIdx">
                                                <div class="text-xs text-slate-600 flex justify-between font-semibold">
                                                    <span x-text="f.name"></span>
                                                    <span class="font-bold text-indigo-650" x-text="f.rooms_count + ' Rooms'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="hasMultipleFloors === 'no'">
                            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm text-xs text-slate-655 space-y-2 font-semibold">
                                <span class="font-bold text-slate-700">General Layout Structure:</span>
                                <p x-text="selectedCategoryType === 'apartment' ? 'Single unit apartment flat setup' : (rooms.length + ' Rooms / Spaces layout')"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Complete Spaces Breakdown Table -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-3">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Configure Rooms List</h4>
                    <div class="overflow-x-auto text-xs">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase text-[9px] tracking-wider">
                                    <th class="py-2">Room / Unit</th>
                                    <th class="py-2">Lease Model</th>
                                    <th class="py-2 text-center">Beds</th>
                                    <th class="py-2 text-center">Max Capacity</th>
                                    <th class="py-2 text-right">Price TZS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(rm, rIdx) in rooms" :key="rIdx">
                                    <tr class="border-b border-slate-100 font-medium text-slate-700">
                                        <td class="py-2 font-bold" x-text="rm.room_number"></td>
                                        <td class="py-2 text-slate-550 capitalize" x-text="rm.rental_model.replace('_', ' ')"></td>
                                        <td class="py-2 text-center" x-text="rm.rental_model === 'per_bed' ? rm.beds_count : '-'"></td>
                                        <td class="py-2 text-center" x-text="rm.capacity"></td>
                                        <td class="py-2 text-right font-bold text-indigo-900" x-text="parseInt(rm.price).toLocaleString() + ' TZS'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- STEP 8: PHOTOS & SUBMISSION -->
            <div x-show="step == 8" x-transition.fade.duration.200ms class="space-y-6" style="display:none;">
                <h3 class="text-lg font-bold text-slate-900">Showcase Photos & Summary Review</h3>
                <p class="text-xs text-slate-500">Review your property setup details before submitting. Add showcase photos to attract tenants.</p>

                <!-- Showcase Photos Drag Zone -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Upload Property Showcase Photos</label>
                    <div class="mt-2 flex justify-center rounded-3xl border-2 border-dashed border-slate-200 px-6 py-10 hover:border-indigo-500 hover:bg-indigo-50/5 transition-all duration-300 relative cursor-pointer group">
                        <input id="images" name="images[]" type="file" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="previewImages($event)">
                        <div class="text-center space-y-2 pointer-events-none">
                            <div class="inline-flex p-3 bg-white border border-slate-100 rounded-2xl shadow-sm text-slate-400 group-hover:text-indigo-600 transition-colors">
                                <i data-lucide="image-plus" class="w-8 h-8"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-600">
                                <span class="text-indigo-650 hover:underline">Upload showcase photos</span>
                                <span class="text-slate-400 pl-1">or drag & drop</span>
                            </div>
                            <p class="text-xs text-slate-400">PNG, JPG, WEBP, GIF up to 5MB per file</p>
                        </div>
                    </div>

                    <!-- Previews Grid -->
                    <div x-show="previews.length > 0" x-transition class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4" style="display:none;">
                        <template x-for="(src, idx) in previews" :key="idx">
                            <div class="relative aspect-video rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                                <img :src="src" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                    <span class="text-white text-xs font-semibold px-2 bg-slate-900/80 rounded-full" x-text="'Image ' + (idx + 1)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Error outputs -->
            @if ($errors->any())
                <div class="mt-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                    <ul class="list-disc pl-5 text-xs text-rose-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Navigation Actions -->
            <div class="pt-8 mt-8 border-t border-slate-150 flex items-center justify-between gap-4">
                <button type="button" @click="saveDraftAction()" class="px-5 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                    Save as Draft & Exit
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" x-show="step > 1" @click="prevAction()" class="px-5 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors" style="display:none;">
                        Previous
                    </button>

                    <button type="button" x-show="step < 8" @click="nextAction()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                        <span>Save & Continue</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>

                    <button type="button" x-show="step == 8" @click="submitAction()" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-all" style="display:none;">
                        <span>Submit for Verification</span>
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    function propertyWizard() {
        return {
            step: {{ $draft->current_step ?? 1 }},
            action: 'save_draft',
            propertyTypeId: '{{ old('property_type_id', $draft->data['property_type_id'] ?? '') }}',
            rentStructure: '{{ old('rent_structure', $draft->data['rent_structure'] ?? 'per_room') }}',
            price: '{{ old('price', $draft->data['price'] ?? '') }}',
            billingPeriod: '{{ old('billing_period', $draft->data['billing_period'] ?? 'monthly') }}',
            roomsCount: '{{ old('rooms_count', $draft->data['rooms_count'] ?? '1') }}',
            hasMultipleFloors: '{{ old('has_multiple_floors', $draft->data['has_multiple_floors'] ?? 'no') }}',
            regionId: '{{ old('region_id', $draft->data['region_id'] ?? '') }}',
            districtId: '{{ old('district_id', $draft->data['district_id'] ?? '') }}',
            wardId: '{{ old('ward_id', $draft->data['ward_id'] ?? '') }}',
            latitude: '{{ old('latitude', $draft->data['latitude'] ?? '-6.7801') }}',
            longitude: '{{ old('longitude', $draft->data['longitude'] ?? '39.2033') }}',
            regions: @json($regions),
            districts: @json($districts),
            wards: @json($wards),
            propertyTypes: @json($propertyTypes),

            // Loop data structures for dynamic inputs
            buildings: {!! json_encode($draft->data['buildings'] ?? [
                [
                    'name' => 'Block A',
                    'floors' => [
                        ['name' => 'Ground Floor', 'rooms_count' => 4],
                        ['name' => '1st Floor', 'rooms_count' => 4]
                    ]
                ]
            ]) !!},

            rooms: {!! json_encode($draft->data['rooms'] ?? []) !!},
            assets: {!! json_encode($draft->data['assets'] ?? []) !!},
            selectedRoomIndex: 0,

            previews: @json(array_map(fn($p) => asset('storage/' . $p), $draft->data['temp_images'] ?? [])),
            previewImages(event) {
                this.previews = [];
                const files = event.target.files;
                for (let i = 0; i < files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push(e.target.result);
                    };
                    reader.readAsDataURL(files[i]);
                }
            },

            map: null,
            marker: null,

            init() {
                this.$watch('step', value => {
                    if (value == 2) {
                        this.initMap();
                    }
                });
                if (this.step == 2) {
                    this.initMap();
                }
            },

            initMap() {
                setTimeout(() => {
                    if (this.map) return;
                    this.map = L.map('map').setView([parseFloat(this.latitude), parseFloat(this.longitude)], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.map);

                    this.marker = L.marker([parseFloat(this.latitude), parseFloat(this.longitude)], {draggable: true}).addTo(this.map);

                    this.map.on('click', e => {
                        this.latitude = e.latlng.lat.toFixed(7);
                        this.longitude = e.latlng.lng.toFixed(7);
                        this.marker.setLatLng(e.latlng);
                    });

                    this.marker.on('dragend', e => {
                        const position = this.marker.getLatLng();
                        this.latitude = position.lat.toFixed(7);
                        this.longitude = position.lng.toFixed(7);
                    });
                }, 100);
            },

            get filteredDistricts() {
                if (!this.regionId) return [];
                return this.districts.filter(d => d.region_id == this.regionId);
            },

            get filteredWards() {
                if (!this.districtId) return [];
                return this.wards.filter(w => w.district_id == this.districtId);
            },

            get selectedCategoryType() {
                const type = this.propertyTypes.find(t => t.id == this.propertyTypeId);
                return type ? type.slug : '';
            },

            propertyTypeName() {
                const type = this.propertyTypes.find(t => t.id == this.propertyTypeId);
                return type ? type.name : 'Unknown';
            },

            addBuilding() {
                this.buildings.push({
                    name: 'Block ' + String.fromCharCode(65 + this.buildings.length),
                    floors: [
                        {name: 'Ground Floor', rooms_count: 4},
                        {name: '1st Floor', rooms_count: 4}
                    ]
                });
                setTimeout(() => lucide.createIcons(), 50);
            },

            removeBuilding(idx) {
                if (this.buildings.length > 1) {
                    this.buildings.splice(idx, 1);
                }
            },

            addFloor(bIdx) {
                const floorsCount = this.buildings[bIdx].floors.length;
                let suffix = 'th Floor';
                if (floorsCount == 1) suffix = '1st Floor';
                else if (floorsCount == 2) suffix = '2nd Floor';
                else if (floorsCount == 3) suffix = '3rd Floor';
                
                this.buildings[bIdx].floors.push({
                    name: floorsCount == 0 ? 'Ground Floor' : (floorsCount) + suffix,
                    rooms_count: 4
                });
                setTimeout(() => lucide.createIcons(), 50);
            },

            removeFloor(bIdx, fIdx) {
                if (this.buildings[bIdx].floors.length > 1) {
                    this.buildings[bIdx].floors.splice(fIdx, 1);
                }
            },

            toggleAmenity(roomIdx, amenityId, isChecked) {
                if (!this.rooms[roomIdx].amenities) {
                    this.rooms[roomIdx].amenities = [];
                }
                if (isChecked) {
                    if (!this.rooms[roomIdx].amenities.includes(amenityId)) {
                        this.rooms[roomIdx].amenities.push(amenityId);
                    }
                } else {
                    this.rooms[roomIdx].amenities = this.rooms[roomIdx].amenities.filter(id => id != amenityId);
                }
            },

            isRoomComplete(idx) {
                const room = this.rooms[idx];
                if (!room) return false;
                return room.room_number && room.room_number.trim() !== '' &&
                       room.capacity && parseInt(room.capacity) > 0 &&
                       room.price !== null && room.price !== '' && parseFloat(room.price) >= 0;
            },

            addAsset() {
                this.assets.push({
                    category_id: '',
                    asset_name: '',
                    quantity: 1,
                    condition: 'good',
                    room_key: ''
                });
                setTimeout(() => lucide.createIcons(), 50);
            },

            removeAsset(idx) {
                this.assets.splice(idx, 1);
            },

            nextAction() {
                this.action = 'next';
                this.$nextTick(() => {
                    document.getElementById('wizardForm').submit();
                });
            },

            prevAction() {
                this.action = 'previous';
                this.$nextTick(() => {
                    document.getElementById('wizardForm').submit();
                });
            },

            saveDraftAction() {
                this.action = 'save_draft';
                this.$nextTick(() => {
                    document.getElementById('wizardForm').submit();
                });
            },

            submitAction() {
                const form = document.getElementById('wizardForm');
                form.action = "{{ route('owner.properties.wizard.submit') }}";
                form.submit();
            }
        }
    }
</script>
@endsection
