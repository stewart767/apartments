@extends('layouts.public')

@section('title', 'Browse Verified Accommodations - RelianceHomes Marketplace')
@section('meta_description', 'Search and filter active student hostels, apartments, houses and bedsitters. Use advanced filters to find places near your college in Tanzania.')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
     x-data="{
        ...searchFilter(),
        viewMode: 'grid',
        mobileFiltersOpen: false,
        quickViewOpen: false,
        quickViewData: null,
        openQuickView(prop) {
            this.quickViewData = prop;
            this.quickViewOpen = true;
            this.$nextTick(() => { lucide.createIcons(); });
        }
     }">

    <!-- 1. Breadcrumbs & Top Summary -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/80 pb-6 mb-8">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ url('/') }}" class="hover:text-indigo-600 transition-colors">Home</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-slate-800">Accommodations Marketplace</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Discover Verified Accommodations
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Showing <strong class="text-slate-900">{{ $properties->total() }}</strong> verified rooms, hostels & apartments available for rent.
            </p>
        </div>

        <!-- Controls: Mobile Filter Button, View Mode Toggle -->
        <div class="flex items-center gap-3">
            <!-- Mobile Filters Toggle -->
            <button type="button" @click="mobileFiltersOpen = true"
                    class="lg:hidden inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-indigo-600"></i>
                <span>Filters</span>
                @if(request()->anyFilled(['q', 'region_id', 'property_type_id', 'rent_structure', 'institution_id', 'min_price', 'max_price', 'verified']))
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                @endif
            </button>

            <!-- Grid vs List View Toggle -->
            <div class="hidden sm:flex items-center bg-slate-100 p-1 rounded-2xl border border-slate-200/60">
                <button type="button" @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        class="p-2 rounded-xl transition-all" title="Grid View">
                    <i data-lucide="grid" class="w-4 h-4"></i>
                </button>
                <button type="button" @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        class="p-2 rounded-xl transition-all" title="List View">
                    <i data-lucide="list" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Active Filter Badges Pill Bar -->
    @if(request()->anyFilled(['q', 'region_id', 'district_id', 'property_type_id', 'rent_structure', 'institution_id', 'min_price', 'max_price', 'verified']))
        <div class="flex flex-wrap items-center gap-2 mb-6 p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100 text-xs">
            <span class="font-bold text-slate-500 text-[11px] uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-indigo-600"></i> Active Filters:
            </span>

            @if(request('q'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-sm">
                    Keyword: "{{ request('q') }}"
                    <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="hover:text-rose-600"><i data-lucide="x" class="w-3 h-3"></i></a>
                </span>
            @endif

            @if(request('rent_structure'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-sm">
                    Structure: {{ str_replace('_', ' ', request('rent_structure')) }}
                    <a href="{{ request()->fullUrlWithQuery(['rent_structure' => null]) }}" class="hover:text-rose-600"><i data-lucide="x" class="w-3 h-3"></i></a>
                </span>
            @endif

            @if(request('max_price'))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-sm">
                    Max: TZS {{ number_format(request('max_price')) }}
                    <a href="{{ request()->fullUrlWithQuery(['max_price' => null]) }}" class="hover:text-rose-600"><i data-lucide="x" class="w-3 h-3"></i></a>
                </span>
            @endif

            @if(request('verified') == 1)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold shadow-sm">
                    Verified Stays Only
                    <a href="{{ request()->fullUrlWithQuery(['verified' => null]) }}" class="hover:text-rose-600"><i data-lucide="x" class="w-3 h-3"></i></a>
                </span>
            @endif

            <a href="{{ route('properties.search') }}" class="ml-auto font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                Clear all filters
            </a>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- 2. Filters Sidebar (Desktop) -->
        <aside class="hidden lg:block w-80 bg-white border border-slate-200/90 rounded-3xl p-6 shadow-sm flex-shrink-0 h-fit space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-indigo-50 text-indigo-600">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm font-extrabold text-slate-900">Filter Stays</h3>
                </div>
                <a href="{{ route('properties.search') }}" class="text-xs text-indigo-600 font-bold hover:underline">Reset</a>
            </div>

            <form action="{{ route('properties.search') }}" method="GET" class="space-y-5">
                <!-- Keyword search -->
                <div>
                    <label for="q" class="block text-xs font-bold text-slate-700 mb-1.5">Search Keywords</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="e.g. Hostels, Ubungo, AC"
                               class="block w-full pl-9 pr-3.5 py-2.5 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 bg-slate-50/50">
                    </div>
                </div>

                <!-- Region Selector -->
                <div>
                    <label for="region_id" class="block text-xs font-bold text-slate-700 mb-1.5">Region</label>
                    <select name="region_id" id="region_id" x-model="regionId" @change="districtId = ''; wardId = ''"
                            class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                        <option value="">Any Region</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg->id }}">{{ $reg->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- District Selector -->
                <div>
                    <label for="district_id" class="block text-xs font-bold text-slate-700 mb-1.5">District</label>
                    <select name="district_id" id="district_id" x-model="districtId" @change="wardId = ''" :disabled="!regionId"
                            class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 disabled:opacity-40">
                        <option value="">Any District</option>
                        <template x-for="d in filteredDistricts" :key="d.id">
                            <option :value="d.id" x-text="d.name" :selected="d.id == districtId"></option>
                        </template>
                    </select>
                </div>

                <!-- Campus Proximity -->
                <div>
                    <label for="institution_id" class="block text-xs font-bold text-slate-700 mb-1.5">College / Campus</label>
                    <select name="institution_id" id="institution_id"
                            class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                        <option value="">All Campuses</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Rent Model (Structure) -->
                <div>
                    <label for="rent_structure" class="block text-xs font-bold text-slate-700 mb-1.5">Accommodation Type</label>
                    <select name="rent_structure" id="rent_structure"
                            class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-900 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600">
                        <option value="">Any Type</option>
                        <option value="per_bed" {{ request('rent_structure') === 'per_bed' ? 'selected' : '' }}>🛏️ Per Bed (Hostel)</option>
                        <option value="per_room" {{ request('rent_structure') === 'per_room' ? 'selected' : '' }}>🚪 Per Room (Private)</option>
                        <option value="entire_property" {{ request('rent_structure') === 'entire_property' ? 'selected' : '' }}>🏠 Entire Apartment/House</option>
                    </select>
                </div>

                <!-- Price Budget Range -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Budget Limit (TZS / month)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                               class="block w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 bg-slate-50/50">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                               class="block w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 bg-slate-50/50">
                    </div>
                    <!-- Quick Budget Pills -->
                    <div class="mt-2 flex flex-wrap gap-1">
                        <a href="{{ request()->fullUrlWithQuery(['max_price' => 150000]) }}" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-[10px] font-bold text-slate-600">
                            &lt; 150k
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['min_price' => 150000, 'max_price' => 300000]) }}" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-[10px] font-bold text-slate-600">
                            150k - 300k
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['min_price' => 300000, 'max_price' => 600000]) }}" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-[10px] font-bold text-slate-600">
                            300k - 600k
                        </a>
                    </div>
                </div>

                <!-- Verified Only Checkbox -->
                <div class="pt-2">
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100 cursor-pointer">
                        <input type="checkbox" name="verified" value="1" {{ request('verified') == 1 ? 'checked' : '' }}
                               class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <span class="text-xs font-bold text-emerald-900 flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                            Physically Verified Stays Only
                        </span>
                    </label>
                </div>

                <!-- Amenities Multi-Select -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Amenities Included</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($amenitiesList as $amenity)
                            <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer hover:text-indigo-600">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                       {{ in_array($amenity->id, (array)request('amenities', [])) ? 'checked' : '' }}
                                       class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                <span>{{ $amenity->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Action Button -->
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply Filter</span>
                </button>
            </form>
        </aside>

        <!-- 3. Mobile Filters Slide-Over Drawer -->
        <div x-show="mobileFiltersOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <div x-show="mobileFiltersOpen" @click="mobileFiltersOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="mobileFiltersOpen" class="pointer-events-auto w-screen max-w-sm bg-white shadow-2xl flex flex-col p-6 space-y-6 overflow-y-auto">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <h3 class="text-base font-extrabold text-slate-900">Filter Accommodations</h3>
                                <button @click="mobileFiltersOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>
                            
                            <!-- Same form replicated for mobile -->
                            <form action="{{ route('properties.search') }}" method="GET" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Keywords</label>
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by area, college..." class="w-full px-3.5 py-2.5 border rounded-2xl text-xs font-semibold">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Accommodation Type</label>
                                    <select name="rent_structure" class="w-full px-3.5 py-2.5 border rounded-2xl text-xs font-semibold">
                                        <option value="">Any</option>
                                        <option value="per_bed">Hostel Bed</option>
                                        <option value="per_room">Single Room</option>
                                        <option value="entire_property">Full Apartment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Campus</label>
                                    <select name="institution_id" class="w-full px-3.5 py-2.5 border rounded-2xl text-xs font-semibold">
                                        <option value="">All Campuses</option>
                                        @foreach($institutions as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="w-full py-3 rounded-2xl bg-indigo-600 text-white font-bold text-xs">
                                    Show Matching Stays
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Main Products Marketplace Content -->
        <main class="flex-1">
            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($properties as $prop)
                    <div class="group bg-white border border-slate-200/90 hover:border-indigo-300 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full overflow-hidden">
                        
                        <!-- Image Container -->
                        <div class="relative h-56 bg-slate-100 overflow-hidden">
                            <img src="{{ $prop->display_image }}" alt="{{ $prop->property_name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                            <!-- Top Badges -->
                            <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    @if($prop->verification_status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-600/95 backdrop-blur-md text-white text-[9px] font-black uppercase tracking-wider rounded-lg shadow-sm">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i> Verified
                                        </span>
                                    @endif
                                </div>

                                <button type="button"
                                        @click="toggleWishlist({
                                            id: {{ $prop->id }},
                                            name: '{{ addslashes($prop->property_name) }}',
                                            price: {{ $prop->starting_price }},
                                            billing: '{{ $prop->billing_period_label }}',
                                            image: '{{ $prop->display_image }}',
                                            location: '{{ addslashes($prop->district->name ?? 'Dar es Salaam') }}'
                                        })"
                                        class="p-2 rounded-full bg-white/90 hover:bg-white text-slate-600 shadow-md transition-all hover:scale-110"
                                        :class="isWishlisted({{ $prop->id }}) ? 'text-rose-600 !bg-rose-50' : 'text-slate-600'">
                                    <i data-lucide="heart" class="w-4 h-4" :class="isWishlisted({{ $prop->id }}) ? 'fill-rose-500 text-rose-500' : ''"></i>
                                </button>
                            </div>

                            <!-- Rating overlay -->
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-xs">
                                <span class="bg-slate-900/80 backdrop-blur-sm px-2.5 py-0.5 rounded-lg text-[11px] font-bold">
                                    {{ $prop->district->name ?? 'Dar es Salaam' }}
                                </span>
                                <span class="bg-amber-400 text-slate-950 font-black px-2 py-0.5 rounded-lg text-[10px] shadow-sm">
                                    ★ {{ $prop->average_rating }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
                            <div>
                                <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-wider">
                                    {{ $prop->propertyType->name ?? 'Hostel' }}
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1 mt-0.5">
                                    <a href="{{ route('properties.show', $prop->id) }}">{{ $prop->property_name }}</a>
                                </h3>
                                @if($prop->institution)
                                    <p class="text-xs text-slate-500 font-medium mt-1">
                                        {{ $prop->distance_from_institution }} km from {{ $prop->institution->name }}
                                    </p>
                                @endif
                            </div>

                            <div class="border-t border-slate-100 pt-4 flex items-center justify-between">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">From</span>
                                    <p class="text-base font-black text-slate-950">
                                        TZS {{ number_format($prop->starting_price) }}
                                        <span class="text-xs font-semibold text-slate-500">/{{ $prop->billing_period_label }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            @click="openQuickView({
                                                id: {{ $prop->id }},
                                                name: '{{ addslashes($prop->property_name) }}',
                                                type: '{{ $prop->propertyType->name ?? 'Hostel' }}',
                                                image: '{{ $prop->display_image }}',
                                                price: '{{ number_format($prop->starting_price) }}',
                                                billing: '{{ $prop->billing_period_label }}',
                                                address: '{{ addslashes($prop->address) }}',
                                                description: '{{ addslashes(Str::limit(strip_tags($prop->description), 160)) }}',
                                                url: '{{ route('properties.show', $prop->id) }}'
                                            })"
                                            class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl" title="Quick View">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <a href="{{ route('properties.show', $prop->id) }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">
                                        View Stay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-20 bg-white border border-slate-200 rounded-3xl p-8 space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-indigo-50 flex items-center justify-center text-indigo-400">
                            <i data-lucide="search-x" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">No accommodations matched your filters</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Try widening your budget, selecting another campus, or clearing active filters.</p>
                        </div>
                        <a href="{{ route('properties.search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 text-white text-xs font-bold shadow-md hover:bg-indigo-700">
                            Reset All Filters
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" class="space-y-4" style="display: none;">
                @foreach($properties as $prop)
                    <div class="group bg-white border border-slate-200/90 hover:border-indigo-300 rounded-3xl p-4 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row gap-5 items-center">
                        <div class="relative w-full sm:w-56 h-44 rounded-2xl overflow-hidden bg-slate-100 flex-shrink-0">
                            <img src="{{ $prop->display_image }}" alt="{{ $prop->property_name }}" class="w-full h-full object-cover">
                            @if($prop->verification_status === 'approved')
                                <span class="absolute top-2 left-2 px-2 py-0.5 bg-emerald-600 text-white text-[9px] font-black uppercase rounded-lg">
                                    Verified
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 w-full space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-600 uppercase">{{ $prop->propertyType->name ?? 'Hostel' }}</span>
                                <span class="text-xs font-extrabold text-amber-500">★ {{ $prop->average_rating }}</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                <a href="{{ route('properties.show', $prop->id) }}">{{ $prop->property_name }}</a>
                            </h3>
                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $prop->address }}
                            </p>
                            @if($prop->institution)
                                <p class="text-xs text-slate-500 font-semibold">
                                    🎓 {{ $prop->distance_from_institution }} km from {{ $prop->institution->name }}
                                </p>
                            @endif
                        </div>
                        <div class="w-full sm:w-auto text-left sm:text-right border-t sm:border-t-0 sm:border-l border-slate-100 pt-3 sm:pt-0 sm:pl-6 flex sm:flex-col justify-between sm:justify-center items-center sm:items-end gap-3 flex-shrink-0">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Rates From</span>
                                <p class="text-lg font-black text-slate-950">
                                    TZS {{ number_format($prop->starting_price) }}
                                    <span class="text-xs font-normal text-slate-500">/{{ $prop->billing_period_label }}</span>
                                </p>
                            </div>
                            <a href="{{ route('properties.show', $prop->id) }}" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md">
                                Reserve Bed
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $properties->links() }}
            </div>
        </main>
    </div>

    <!-- Quick View Modal (Re-used) -->
    <div x-show="quickViewOpen" x-cloak class="relative z-50" role="dialog" aria-modal="true">
        <div x-show="quickViewOpen" @click="quickViewOpen = false" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex min-h-full items-center justify-center p-4">
            <div x-show="quickViewOpen" class="relative overflow-hidden rounded-3xl bg-white text-left shadow-2xl sm:max-w-xl w-full border border-slate-200">
                <template x-if="quickViewData">
                    <div>
                        <div class="relative h-64 bg-slate-100">
                            <img :src="quickViewData.image" :alt="quickViewData.name" class="w-full h-full object-cover">
                            <button @click="quickViewOpen = false" class="absolute top-3 right-3 p-2 rounded-full bg-slate-900/60 text-white">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-black text-slate-900" x-text="quickViewData.name"></h3>
                                    <p class="text-xs text-slate-500 mt-1" x-text="quickViewData.address"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-base font-black text-indigo-600" x-text="'TZS ' + quickViewData.price + '/' + quickViewData.billing"></p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600" x-text="quickViewData.description"></p>
                            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                                <button type="button" @click="quickViewOpen = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-slate-600">Close</button>
                                <a :href="quickViewData.url" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">View Listing</a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function searchFilter() {
        return {
            regionId: '{{ request('region_id', '') }}',
            districtId: '{{ request('district_id', '') }}',
            wardId: '{{ request('ward_id', '') }}',
            districts: @json($districts->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'region_id' => $d->region_id])),
            wards: @json($wards->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'district_id' => $w->district_id])),

            init() {
                const initRegion = '{{ request('region_id', '') }}';
                const initDistrict = '{{ request('district_id', '') }}';
                const initWard = '{{ request('ward_id', '') }}';

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
</script>
@endsection
