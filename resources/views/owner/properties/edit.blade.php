@extends('layouts.admin')

@section('title', 'Edit Property')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-6 mb-8">
        <a href="{{ route('owner.properties') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Properties</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Edit Property Listing</h1>
        <p class="mt-1 text-sm text-slate-500">
            Modify details of your hostel, apartment or bedsitter listing. Edits will require re-verification.
        </p>
    </div>

    <!-- Success Message if any -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-55 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2 text-sm font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Form container -->
    <div class="bg-white border border-slate-100 p-8 sm:p-10 rounded-3xl shadow-xl shadow-slate-100/50">
        <form method="POST" action="{{ route('owner.properties.update', $property->id) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Section 1: Basic Information -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Basic Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Property Name -->
                    <div>
                        <label for="property_name" class="block text-sm font-semibold text-slate-700">Property / Hostel Name</label>
                        <input id="property_name" name="property_name" type="text" required value="{{ old('property_name', $property->property_name) }}" placeholder="e.g. Mwenge Heights Executive"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-650 focus:border-indigo-600 sm:text-sm">
                        @error('property_name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Property Type -->
                    <div>
                        <label for="property_type_id" class="block text-sm font-semibold text-slate-700">Property Category</label>
                        <select id="property_type_id" name="property_type_id" required
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-650 focus:border-indigo-600 sm:text-sm">
                            <option value="" disabled>Select Category...</option>
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type->id }}" {{ old('property_type_id', $property->property_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('property_type_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Describe utilities, backup power, water availability, security guards, laundry services etc..."
                              class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-650 focus:border-indigo-600 sm:text-sm">{{ old('description', $property->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 2: Layout & Pricing -->
            <div class="space-y-4 pt-6 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Pricing & Leasing Model</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Rent Structure -->
                    <div>
                        <label for="rent_structure" class="block text-sm font-semibold text-slate-700">Rent Structure</label>
                        <select id="rent_structure" name="rent_structure" required
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-650 focus:border-indigo-600 sm:text-sm">
                            <option value="" disabled>Select Model...</option>
                            <option value="per_bed" {{ old('rent_structure', $property->rent_structure) === 'per_bed' ? 'selected' : '' }}>Per Bed</option>
                            <option value="per_room" {{ old('rent_structure', $property->rent_structure) === 'per_room' ? 'selected' : '' }}>Per Room</option>
                            <option value="per_unit" {{ old('rent_structure', $property->rent_structure) === 'per_unit' ? 'selected' : '' }}>Per Apartment Unit</option>
                            <option value="entire_property" {{ old('rent_structure', $property->rent_structure) === 'entire_property' ? 'selected' : '' }}>Entire Property</option>
                        </select>
                        @error('rent_structure')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Base Price -->
                    <div>
                        <label for="price" class="block text-sm font-semibold text-slate-700">Base Rent Price (TZS)</label>
                        <input id="price" name="price" type="number" step="1" required value="{{ old('price', $property->price) }}" placeholder="e.g. 150000"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-650 focus:border-indigo-600 sm:text-sm">
                        @error('price')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Period -->
                    <div>
                        <label for="billing_period" class="block text-sm font-semibold text-slate-700">Lease Billing Period</label>
                        <select id="billing_period" name="billing_period" required
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-650 focus:border-indigo-600 sm:text-sm">
                            <option value="" disabled>Select Period...</option>
                            <option value="daily" {{ old('billing_period', $property->billing_period) === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('billing_period', $property->billing_period) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('billing_period', $property->billing_period) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="semester" {{ old('billing_period', $property->billing_period) === 'semester' ? 'selected' : '' }}>Per Semester</option>
                            <option value="six_months" {{ old('billing_period', $property->billing_period) === 'six_months' ? 'selected' : '' }}>Six Months</option>
                            <option value="annual" {{ old('billing_period', $property->billing_period) === 'annual' ? 'selected' : '' }}>Annual</option>
                        </select>
                        @error('billing_period')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Location Details (Dynamic Alpine select) -->
            <div class="space-y-4 pt-6 border-t border-slate-100" x-data="locationPicker()">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Location Hierarchy</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Region -->
                    <div>
                        <label for="region_id" class="block text-sm font-semibold text-slate-700">Region</label>
                        <select id="region_id" name="region_id" x-model="regionId" @change="districtId = ''; wardId = ''" required
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <option value="" disabled>Select Region...</option>
                            @foreach($regions as $r)
                                <option value="{{ $r->id }}" {{ old('region_id', $property->region_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('region_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- District -->
                    <div>
                        <label for="district_id" class="block text-sm font-semibold text-slate-700">District</label>
                        <select id="district_id" name="district_id" x-model="districtId" @change="wardId = ''" required :disabled="!regionId"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                            <option value="" disabled>Select District...</option>
                            <template x-for="d in filteredDistricts" :key="d.id">
                                <option :value="d.id" x-text="d.name" :selected="d.id == districtId"></option>
                            </template>
                        </select>
                        @error('district_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ward -->
                    <div>
                        <label for="ward_id" class="block text-sm font-semibold text-slate-700">Ward</label>
                        <select id="ward_id" name="ward_id" x-model="wardId" required :disabled="!districtId"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm disabled:opacity-50">
                            <option value="" disabled>Select Ward...</option>
                            <template x-for="w in filteredWards" :key="w.id">
                                <option :value="w.id" x-text="w.name" :selected="w.id == wardId"></option>
                            </template>
                        </select>
                        @error('ward_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Street -->
                    <div>
                        <label for="street" class="block text-sm font-semibold text-slate-700">Street Name (Optional)</label>
                        <input id="street" name="street" type="text" value="{{ old('street', $property->street) }}" placeholder="e.g. Mlalakuwa Road"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('street')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Physical Address -->
                    <div>
                        <label for="address" class="block text-sm font-semibold text-slate-700">Physical Address / Directions</label>
                        <input id="address" name="address" type="text" required value="{{ old('address', $property->address) }}" placeholder="Plot details, landmark, building number..."
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('address')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 4: Proximity (Optional) -->
            <div class="space-y-4 pt-6 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Institution Proximity</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nearby Institution -->
                    <div>
                        <label for="nearby_institution_id" class="block text-sm font-semibold text-slate-700">Nearest College/University</label>
                        <select id="nearby_institution_id" name="nearby_institution_id"
                                class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 bg-white rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                            <option value="">None Selected</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" {{ old('nearby_institution_id', $property->nearby_institution_id) == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                            @endforeach
                        </select>
                        @error('nearby_institution_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Distance -->
                    <div>
                        <label for="distance_from_institution" class="block text-sm font-semibold text-slate-700">Distance (in Kilometers)</label>
                        <input id="distance_from_institution" name="distance_from_institution" type="number" step="0.01" min="0" value="{{ old('distance_from_institution', $property->distance_from_institution) }}" placeholder="e.g. 0.45"
                               class="mt-1.5 block w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 sm:text-sm">
                        @error('distance_from_institution')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 5: Property Images -->
            <div class="space-y-4 pt-6 border-t border-slate-100" x-data="imagePreviewer()">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Property Showcase Images</h3>
                
                <!-- Existing Showcase Images -->
                @if($property->images->isNotEmpty())
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Current Images</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach($property->images as $img)
                                <div class="relative group aspect-video rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                                    <img src="{{ asset('storage/' . $img->file_path) }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity gap-2">
                                        @if($img->is_primary)
                                            <span class="text-white text-[10px] font-bold px-2 py-0.5 bg-emerald-600 rounded-full">Primary</span>
                                        @endif
                                        
                                        <!-- Delete Action button -->
                                        <button type="button" 
                                                onclick="if(confirm('Are you sure you want to delete this image?')) { document.getElementById('delete-image-form-{{ $img->id }}').submit(); }"
                                                class="inline-flex items-center justify-center p-1.5 bg-rose-600 text-white rounded-lg hover:bg-rose-500 transition-colors shadow-md" 
                                                title="Delete image">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Upload New Images -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Upload New Images</label>
                    <div class="mt-2 flex justify-center rounded-3xl border-2 border-dashed border-slate-200 px-6 py-10 hover:border-indigo-500 hover:bg-indigo-50/5 transition-all duration-300 group relative cursor-pointer">
                        <input id="images" name="images[]" type="file" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="previewImages($event)">
                        <div class="text-center space-y-2 pointer-events-none">
                            <div class="inline-flex p-3 bg-white rounded-2xl text-slate-400 group-hover:text-indigo-600 transition-colors shadow-sm border border-slate-100">
                                <i data-lucide="image-plus" class="w-8 h-8"></i>
                            </div>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <span class="font-semibold text-indigo-600 hover:text-indigo-500">Upload files</span>
                                <span class="pl-1">or drag and drop</span>
                            </div>
                            <p class="text-xs text-slate-400">PNG, JPG, WEBP, GIF up to 5MB per file</p>
                        </div>
                    </div>

                    <!-- Previews Grid -->
                    <div x-show="previews.length > 0" x-transition class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4" x-cloak>
                        <template x-for="(src, index) in previews" :key="index">
                            <div class="relative aspect-video rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                                <img :src="src" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                    <span class="text-white text-xs font-semibold px-2.5 py-1 bg-slate-900/80 backdrop-blur-md rounded-full" x-text="'Image ' + (index + 1)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    @error('images')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit & Action Buttons -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                <a href="{{ route('owner.properties') }}" class="px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                    <span>Save Changes</span>
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </div>
        </form>

        <!-- Separate Delete Forms to avoid form nesting issues -->
        @if($property->images->isNotEmpty())
            @foreach($property->images as $img)
                <form id="delete-image-form-{{ $img->id }}" method="POST" action="{{ route('owner.properties.images.destroy', $img->id) }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function locationPicker() {
        return {
            regionId: '',
            districtId: '',
            wardId: '',
            districts: @json($districts->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'region_id' => $d->region_id])),
            wards: @json($wards->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'district_id' => $w->district_id])),

            init() {
                const initRegion = '{{ old('region_id', $property->region_id ?? '') }}';
                const initDistrict = '{{ old('district_id', $property->district_id ?? '') }}';
                const initWard = '{{ old('ward_id', $property->ward_id ?? '') }}';

                if (initRegion) {
                    this.regionId = initRegion;
                    this.$nextTick(() => {
                        if (initDistrict) {
                            this.districtId = initDistrict;
                            this.$nextTick(() => {
                                if (initWard) {
                                    this.wardId = initWard;
                                }
                            });
                        }
                    });
                }
            },

            get filteredDistricts() {
                if (!this.regionId) return [];
                return this.districts.filter(d => String(d.region_id) === String(this.regionId));
            },

            get filteredWards() {
                if (!this.districtId) return [];
                return this.wards.filter(w => String(w.district_id) === String(this.districtId));
            }
        };
    }

    function imagePreviewer() {
        return {
            previews: [],
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
            }
        };
    }
</script>
@endsection
