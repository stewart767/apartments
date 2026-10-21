@extends('layouts.public')

@section('title', $property->property_name . ' - Verified Accommodation in ' . ($property->district->name ?? 'Dar es Salaam'))
@section('meta_description', Str::limit(strip_tags($property->description), 150))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-10"
     x-data="{
        selectedRoomId: '{{ $property->rent_structure === 'entire_property' ? 'all' : '' }}',
        selectedBedId: '',
        selectedPrice: {{ $property->starting_price }},
        selectedBilling: '{{ $property->billing_period_label }}',
        selectedNumber: '{{ $property->rent_structure === 'entire_property' ? 'Entire House' : 'Not selected yet' }}',
        galleryModalOpen: false,
        activePhotoIdx: 0,
        startDate: '{{ date('Y-m-d') }}',
        endDate: '{{ date('Y-m-d', strtotime('+3 months')) }}',
        monthsCount: 3,
        calculateMonths() {
            if (!this.startDate || !this.endDate) return;
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);
            const diffTime = Math.max(0, end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            this.monthsCount = Math.max(1, Math.round(diffDays / 30));
        }
     }">
    
    <!-- 1. Top Breadcrumbs & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div class="space-y-1">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <a href="{{ url('/') }}" class="hover:text-indigo-600 transition-colors">Home</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('properties.search') }}" class="hover:text-indigo-600 transition-colors">Accommodations</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-slate-800">{{ $property->district->name ?? 'Dar es Salaam' }}</span>
            </nav>

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">
                    {{ $property->property_name }}
                </h1>
                @if($property->verification_status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-extrabold uppercase tracking-wider rounded-xl border border-emerald-200/80 shadow-sm">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                        <span>Physically Inspected</span>
                    </span>
                @endif
            </div>

            <p class="text-xs sm:text-sm text-slate-500 flex items-center gap-1.5 pt-0.5">
                <i data-lucide="map-pin" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
                <span>{{ $property->address }}, {{ $property->ward->name ?? '' }}, {{ $property->district->name ?? '' }}, {{ $property->region->name ?? '' }}</span>
            </p>
        </div>

        <!-- Wishlist & Share Quick Actions -->
        <div class="flex items-center gap-3 flex-shrink-0">
            <button type="button"
                    @click="toggleWishlist({
                        id: {{ $property->id }},
                        name: '{{ addslashes($property->property_name) }}',
                        price: {{ $property->starting_price }},
                        billing: '{{ $property->billing_period_label }}',
                        image: '{{ $property->display_image }}',
                        location: '{{ addslashes($property->district->name ?? 'Dar es Salaam') }}'
                    })"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 text-slate-700 text-xs font-bold shadow-sm transition-all"
                    :class="isWishlisted({{ $property->id }}) ? 'text-rose-600 !bg-rose-50 !border-rose-200' : ''">
                <i data-lucide="heart" class="w-4 h-4" :class="isWishlisted({{ $property->id }}) ? 'fill-rose-500' : ''"></i>
                <span x-text="isWishlisted({{ $property->id }}) ? 'Saved to Favorites' : 'Save Stay'"></span>
            </button>

            <button type="button"
                    @click="navigator.clipboard.writeText(window.location.href); showToast('📋 Listing link copied to clipboard!', 'info')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-sm transition-all">
                <i data-lucide="share-2" class="w-4 h-4"></i>
                <span>Share</span>
            </button>
        </div>
    </div>

    <!-- 2. High-Converting Airbnb-Style Photo Showcase Grid -->
    @php
        $gallery = $property->gallery_images;
    @endphp
    <div class="relative rounded-3xl overflow-hidden shadow-lg border border-slate-200/80 bg-slate-900">
        <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-2 h-80 sm:h-[450px]">
            <!-- Hero Main Photo (Left 2 cols, 2 rows) -->
            <div class="md:col-span-2 md:row-span-2 relative overflow-hidden group cursor-pointer"
                 @click="galleryModalOpen = true; activePhotoIdx = 0">
                <img src="{{ $gallery[0] ?? $property->display_image }}" alt="{{ $property->property_name }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-slate-950/20 group-hover:bg-transparent transition-colors"></div>
            </div>

            <!-- Top 2 Right Photos -->
            <div class="hidden md:block relative overflow-hidden group cursor-pointer"
                 @click="galleryModalOpen = true; activePhotoIdx = 1">
                <img src="{{ $gallery[1] ?? $property->display_image }}" alt="Photo 2"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <div class="hidden md:block relative overflow-hidden group cursor-pointer"
                 @click="galleryModalOpen = true; activePhotoIdx = 2">
                <img src="{{ $gallery[2] ?? $property->display_image }}" alt="Photo 3"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>

            <!-- Bottom 2 Right Photos -->
            <div class="hidden md:block relative overflow-hidden group cursor-pointer"
                 @click="galleryModalOpen = true; activePhotoIdx = 3">
                <img src="{{ $gallery[3] ?? $property->display_image }}" alt="Photo 4"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <div class="hidden md:block relative overflow-hidden group cursor-pointer"
                 @click="galleryModalOpen = true; activePhotoIdx = 4">
                <img src="{{ $gallery[4] ?? $property->display_image }}" alt="Photo 5"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
        </div>

        <!-- "Show All Photos" Floating Trigger -->
        <button type="button" @click="galleryModalOpen = true"
                class="absolute bottom-4 right-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/95 hover:bg-white text-slate-900 text-xs font-extrabold shadow-xl backdrop-blur-md transition-all hover:scale-105">
            <i data-lucide="images" class="w-4 h-4"></i>
            <span>View All Photos ({{ count($gallery) }})</span>
        </button>
    </div>

    <!-- 3. Two-Column Main Content & Sticky Booking Box -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        <!-- Left Content Area (2 Cols) -->
        <div class="lg:col-span-2 space-y-10">

            <!-- Verified Landlord & Key Highlights -->
            <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-black text-xl flex items-center justify-center shadow-md">
                            {{ substr($property->owner->name ?? 'Landlord', 0, 1) }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 p-1 bg-emerald-500 text-white rounded-full border-2 border-white text-[10px]">
                            <i data-lucide="check" class="w-3 h-3"></i>
                        </span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-slate-900">Hosted by {{ $property->owner->name ?? 'Verified Property Owner' }}</h3>
                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold">Verified Landlord</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Prompt response rate (under 30 mins) &bull; Official Digital Lease Signatory</p>
                    </div>
                </div>

                <!-- Distance from College Pill -->
                @if($property->institution)
                    <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200/60 text-xs font-semibold text-slate-700">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Proximity</span>
                        <div class="flex items-center gap-1.5 mt-0.5 text-indigo-700 font-bold">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            <span>{{ $property->distance_from_institution }} km from {{ $property->institution->name }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- About Accommodation Description -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-8 space-y-4 shadow-sm">
                <h3 class="text-lg font-black text-slate-950 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
                    <span>About this accommodation</span>
                </h3>
                <div class="text-sm text-slate-600 leading-relaxed space-y-4 whitespace-pre-line font-normal">
                    {!! nl2br(e($property->description)) !!}
                </div>
            </div>

            <!-- Interactive Room & Bed Space Selection Matrix (The E-Commerce Selection Step) -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-8 space-y-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-950 flex items-center gap-2">
                            <i data-lucide="bed" class="w-5 h-5 text-indigo-600"></i>
                            <span>Choose Your Room or Bed Unit</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Select an active vacant space to calculate your lease invoice & reserve immediately.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 badge-pulse"></span>
                        Real-time Vacancy
                    </span>
                </div>

                <!-- ENTIRE PROPERTY Booking Option -->
                @if($property->rent_structure === 'entire_property')
                    <div class="p-5 bg-gradient-to-r from-slate-50 to-indigo-50/30 border-2 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all"
                         :class="selectedRoomId === 'all' ? 'border-indigo-600 bg-indigo-50/50' : 'border-slate-200'">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-base font-extrabold text-slate-900">Entire Residential House / Bungalow</h4>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-bold">Exclusive</span>
                            </div>
                            <p class="text-xs text-slate-500">Full private access to all rooms, kitchen, compound, and parking.</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <span class="text-lg font-black text-slate-950">TZS {{ number_format($property->price) }}</span>
                                <span class="text-xs text-slate-500 font-semibold">/{{ $property->billing_period }}</span>
                            </div>
                            <button @click="selectedRoomId = 'all'; selectedBedId = ''; selectedPrice = {{ $property->price }}; selectedBilling = '{{ $property->billing_period }}'; selectedNumber = 'Entire House'"
                                    class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all"
                                    :class="selectedRoomId === 'all' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'">
                                <span x-text="selectedRoomId === 'all' ? '✓ Selected' : 'Select Unit'"></span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- ROOM SELECTION Option (Per Room) -->
                @if($property->rent_structure === 'per_room' || $property->rent_structure === 'per_unit')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($property->rooms as $room)
                            @php
                                $isRoomOccupied = $room->occupancy >= $room->capacity;
                            @endphp
                            <div class="p-5 border-2 rounded-2xl flex flex-col justify-between space-y-4 transition-all"
                                 :class="selectedRoomId == '{{ $room->id }}' ? 'border-indigo-600 bg-indigo-50/30 shadow-md' : 'border-slate-200 bg-white'">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-base font-extrabold text-slate-900">Room {{ $room->room_number }}</h4>
                                        @if(!$isRoomOccupied)
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-extrabold uppercase tracking-wider">Vacant</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 text-[10px] font-extrabold uppercase tracking-wider">Occupied</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">{{ $room->roomType->name ?? 'Single Room' }} &bull; {{ str_replace('_', ' ', $room->furnished_status) }}</p>
                                </div>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-black text-slate-950">TZS {{ number_format($room->price) }}</p>
                                        <span class="text-[10px] text-slate-400 font-semibold">per {{ $room->billing_period }}</span>
                                    </div>
                                    @if(!$isRoomOccupied)
                                        <button @click="selectedRoomId = '{{ $room->id }}'; selectedBedId = ''; selectedPrice = {{ $room->price }}; selectedBilling = '{{ $room->billing_period }}'; selectedNumber = 'Room {{ $room->room_number }}'"
                                                type="button" class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                                                :class="selectedRoomId == '{{ $room->id }}' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600'">
                                            <span x-text="selectedRoomId == '{{ $room->id }}' ? '✓ Selected' : 'Select'"></span>
                                        </button>
                                    @else
                                        <button disabled class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed">
                                            Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- BED SELECTION Option (Hostel / Bed Spaces) -->
                @if($property->rent_structure === 'per_bed')
                    <div class="space-y-4" x-data="{ activeAccordion: '{{ $property->rooms->first()->id ?? '' }}' }">
                        @foreach($property->rooms as $room)
                            <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                                <!-- Room Title Accordion -->
                                <button type="button" @click="activeAccordion = (activeAccordion === '{{ $room->id }}') ? '' : '{{ $room->id }}'"
                                        class="w-full p-4 sm:p-5 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm sm:text-base font-extrabold text-slate-900">Room {{ $room->room_number }}</h4>
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold">{{ $room->roomType->name ?? 'Double' }}</span>
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-semibold capitalize">{{ $room->gender_accommodation }} only</span>
                                        </div>
                                        <p class="text-xs text-slate-500">Capacity: {{ $room->capacity }} beds &bull; {{ $room->furnished_status }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold text-slate-500 hidden sm:inline">View bed spaces</span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform" :class="activeAccordion === '{{ $room->id }}' ? 'rotate-180' : ''"></i>
                                    </div>
                                </button>

                                <!-- Beds List Grid -->
                                <div x-show="activeAccordion === '{{ $room->id }}'" x-transition class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @forelse($room->beds as $bed)
                                        <div class="p-4 rounded-xl border-2 transition-all flex items-center justify-between"
                                             :class="selectedBedId == '{{ $bed->id }}' ? 'border-indigo-600 bg-white shadow-md' : 'border-slate-200 bg-white'">
                                            <div>
                                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Bed Space</span>
                                                <h5 class="text-sm font-extrabold text-slate-900">{{ $bed->bed_number }}</h5>
                                                <p class="text-xs font-black text-indigo-600 mt-0.5">
                                                    TZS {{ number_format($bed->price) }}<span class="text-[10px] text-slate-400 font-normal">/{{ $bed->billing_period }}</span>
                                                </p>
                                            </div>
                                            <div>
                                                @if($bed->status === 'available')
                                                    <button @click="selectedRoomId = '{{ $room->id }}'; selectedBedId = '{{ $bed->id }}'; selectedPrice = {{ $bed->price }}; selectedBilling = '{{ $bed->billing_period }}'; selectedNumber = 'Room {{ $room->room_number }} - Bed {{ $bed->bed_number }}'"
                                                            type="button" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all"
                                                            :class="selectedBedId == '{{ $bed->id }}' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'">
                                                        <span x-text="selectedBedId == '{{ $bed->id }}' ? '✓ Selected' : 'Choose Bed'"></span>
                                                    </button>
                                                @else
                                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-400 rounded-lg text-[10px] font-bold uppercase">Taken</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 col-span-2">No individual beds registered in this room yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Amenities Grid -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-8 space-y-6 shadow-sm">
                <h3 class="text-lg font-black text-slate-950 flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-indigo-600"></i>
                    <span>Included Facilities & Amenities</span>
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @forelse($property->amenities as $amenity)
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 text-slate-800">
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl flex-shrink-0">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </span>
                            <span class="text-xs font-bold">{{ $amenity->name }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 col-span-3">No amenities listed.</p>
                    @endforelse
                </div>
            </div>

            <!-- Verified Tenant Reviews & Rating Breakdown -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-8 space-y-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-950 flex items-center gap-2">
                            <i data-lucide="star" class="w-5 h-5 text-amber-500 fill-amber-500"></i>
                            <span>Tenant Reviews & Ratings</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Physical feedback from past residents of this building.</p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-slate-900">★ {{ $property->average_rating }}</span>
                        <span class="text-xs text-slate-400 block font-semibold">({{ $property->reviews_count }} verified reviews)</span>
                    </div>
                </div>

                <!-- Review Items -->
                <div class="space-y-6 divide-y divide-slate-100">
                    @forelse($property->reviews as $rev)
                        <div class="pt-6 first:pt-0 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                        {{ substr($rev->tenant->name ?? 'T', 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900">{{ $rev->tenant->name ?? 'Verified Tenant' }}</h4>
                                        <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i> Verified Past Tenant
                                        </span>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-amber-500">★ {{ $rev->average_rating ?? 5.0 }}</span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed italic">
                                "{{ $rev->comment }}"
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 space-y-1">
                            <i data-lucide="message-square" class="w-8 h-8 mx-auto text-slate-300"></i>
                            <p class="text-xs font-semibold">Be the first to leave a review after your tenancy check-in!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Sticky E-Commerce Checkout / Reservation Widget -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 shadow-2xl shadow-indigo-950/10 space-y-6">
                
                <!-- Price Display -->
                <div class="flex items-baseline justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Rent Rate</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl font-black text-slate-950" x-text="'TZS ' + Number(selectedPrice).toLocaleString()">
                                TZS {{ number_format($property->starting_price) }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500" x-text="'/' + selectedBilling">
                                /{{ $property->billing_period_label }}
                            </span>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-[10px] font-black tracking-wide uppercase">
                        Instant Book
                    </span>
                </div>

                <!-- Your Selected Unit Pill -->
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-500">Selected Allocation</span>
                    <div class="flex items-center justify-between text-xs font-bold text-slate-900">
                        <span x-text="selectedNumber">Entire House</span>
                        <span class="text-indigo-700" x-text="'TZS ' + Number(selectedPrice).toLocaleString()"></span>
                    </div>
                </div>

                <!-- Reservation Form -->
                @auth
                    @if(auth()->user()->isTenant())
                        <form action="{{ route('properties.book', $property->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="room_id" :value="selectedRoomId">
                            <input type="hidden" name="bed_id" :value="selectedBedId">

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Move-In Date</label>
                                    <input type="date" name="start_date" x-model="startDate" @change="calculateMonths()" required min="{{ date('Y-m-d') }}"
                                           class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Move-Out / Semester End</label>
                                    <input type="date" name="end_date" x-model="endDate" @change="calculateMonths()" required min="{{ date('Y-m-d', strtotime('+1 month')) }}"
                                           class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                </div>
                            </div>

                            <!-- Transparent Price Breakdown (E-Commerce style) -->
                            <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Rent Rate (<span x-text="monthsCount">3</span> months est.)</span>
                                    <span class="font-bold text-slate-800" x-text="'TZS ' + (selectedPrice * monthsCount).toLocaleString()"></span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span class="flex items-center gap-1">
                                        Refundable Deposit
                                        <i data-lucide="help-circle" class="w-3 h-3 text-slate-400" title="Returned at move-out"></i>
                                    </span>
                                    <span class="font-bold text-slate-800">TZS 50,000</span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Broker / Middleman Fee</span>
                                    <span class="font-bold text-emerald-600 uppercase">FREE (0 TZS)</span>
                                </div>
                                <div class="pt-2 border-t border-slate-100 flex justify-between text-sm font-black text-slate-950">
                                    <span>Estimated First Payment</span>
                                    <span class="text-indigo-600" x-text="'TZS ' + (selectedPrice + 50000).toLocaleString()"></span>
                                </div>
                            </div>

                            <button type="submit" :disabled="!selectedRoomId"
                                    class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Proceed to Reserve Bed</span>
                            </button>
                        </form>
                    @else
                        <div class="p-4 rounded-2xl bg-amber-50 text-amber-800 text-xs font-semibold text-center border border-amber-200">
                            Logged in as a <strong>{{ auth()->user()->role }}</strong>. Please switch to a Tenant account to submit reservations.
                        </div>
                    @endif
                @else
                    <div class="space-y-3 pt-2">
                        <a href="{{ route('login') }}" class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-md shadow-indigo-600/20 transition-all">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            <span>Sign In to Reserve Room</span>
                        </a>
                        <a href="{{ route('register') }}" class="w-full py-3 px-4 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-1 transition-all">
                            <span>New Tenant? Create Free Account</span>
                        </a>
                    </div>
                @endauth

                <!-- Trust Badges & Guarantee Under Form -->
                <div class="pt-4 border-t border-slate-100 space-y-3 text-[11px] text-slate-500">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                        <span><strong>100% Refundable Deposit</strong> if property condition does not match physical inspection upon check-in.</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="file-check-2" class="w-4 h-4 text-indigo-600 flex-shrink-0 mt-0.5"></i>
                        <span><strong>Instant Digital PDF Lease</strong> legally generated with landlord signature upon booking approval.</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="smartphone" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                        <span>Pay directly via M-Pesa Lipa Namba, Tigo Pesa, Airtel Money with instant automated receipts.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Similar Stays Carousel (Bottom Product Recommendations) -->
    @if(isset($similarProperties) && $similarProperties->isNotEmpty())
        <div class="pt-16 border-t border-slate-200/80">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Similar Listings</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-0.5">You May Also Like</h3>
                </div>
                <a href="{{ route('properties.search') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                    View More Stays &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($similarProperties as $sim)
                    <div class="group bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                        <div class="relative h-48 bg-slate-100">
                            <img src="{{ $sim->display_image }}" alt="{{ $sim->property_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-3 left-3 px-2 py-0.5 rounded-lg bg-slate-900/80 text-white text-[10px] font-bold">
                                {{ $sim->propertyType->name ?? 'Hostel' }}
                            </span>
                        </div>
                        <div class="p-5 space-y-3">
                            <h4 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 truncate">
                                <a href="{{ route('properties.show', $sim->id) }}">{{ $sim->property_name }}</a>
                            </h4>
                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $sim->district->name ?? 'Dar es Salaam' }}
                            </p>
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-black text-slate-950">TZS {{ number_format($sim->starting_price) }}/{{ $sim->billing_period_label }}</span>
                                <a href="{{ route('properties.show', $sim->id) }}" class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 5. Fullscreen Gallery Lightbox Modal (Alpine.js) -->
    <div x-show="galleryModalOpen" x-cloak class="relative z-50" role="dialog" aria-modal="true">
        <div x-show="galleryModalOpen" @click="galleryModalOpen = false" class="fixed inset-0 bg-slate-950/90 backdrop-blur-md"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto flex min-h-full items-center justify-center p-4">
            <div x-show="galleryModalOpen" class="relative max-w-4xl w-full bg-slate-950 rounded-3xl overflow-hidden shadow-2xl p-4">
                <div class="flex items-center justify-between text-white pb-4 px-2">
                    <span class="text-xs font-bold" x-text="(activePhotoIdx + 1) + ' / {{ count($gallery) }}'"></span>
                    <button @click="galleryModalOpen = false" class="p-2 text-white hover:text-slate-300">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                @php $galleryJson = json_encode($gallery); @endphp
                <div class="relative h-96 sm:h-[500px]" x-data="{ photos: {{ $galleryJson }} }">
                    <img :src="photos[activePhotoIdx]" alt="Photo Preview" class="w-full h-full object-contain rounded-2xl">
                    <button @click="activePhotoIdx = (activePhotoIdx === 0) ? photos.length - 1 : activePhotoIdx - 1"
                            class="absolute left-3 top-1/2 -translate-y-1/2 p-3 bg-white/20 hover:bg-white/40 text-white rounded-full">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
                    <button @click="activePhotoIdx = (activePhotoIdx === photos.length - 1) ? 0 : activePhotoIdx + 1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-3 bg-white/20 hover:bg-white/40 text-white rounded-full">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
