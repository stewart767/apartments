@extends('layouts.public')

@section('title', 'RelianceHomes - Find & Book Verified Hostels, Apartments & Student Rooms')
@section('meta_description', 'Search and reserve verified student hostels, single rooms, bedsitters and apartments near major universities in Tanzania with 0% broker fees.')

@section('content')
<div x-data="{
    activeTab: 'all',
    quickViewOpen: false,
    quickViewData: null,
    openQuickView(prop) {
        this.quickViewData = prop;
        this.quickViewOpen = true;
        this.$nextTick(() => { lucide.createIcons(); });
    }
}">

    @php
        $heroProp = $featuredProperties->first() ?? $recentProperties->first();
        $subHero1 = $featuredProperties->skip(1)->first() ?? $recentProperties->skip(1)->first() ?? $heroProp;
        $subHero2 = $featuredProperties->skip(2)->first() ?? $recentProperties->skip(2)->first() ?? $heroProp;
    @endphp

    <!-- 1. Visual-First Modern Hero (No Walls of Text - Real Imagery Showcase) -->
    <section class="relative bg-slate-950 overflow-hidden pt-8 pb-16 lg:pt-12 lg:pb-20">
        <!-- Glow effects -->
        <div class="absolute inset-0 opacity-25 pointer-events-none">
            <div class="absolute top-1/3 left-1/3 w-[650px] h-[450px] bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 rounded-full blur-[140px]"></div>
            <div class="absolute bottom-0 right-10 w-[450px] h-[450px] bg-blue-600 rounded-full blur-[120px]"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- 2-Column Visual Hero Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Left Column: Compact Punchy Search & Direct Action (Minimal Words) -->
                <div class="lg:col-span-5 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-400/20 text-indigo-300 text-xs font-bold uppercase tracking-wider">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-400 badge-pulse"></span>
                        <span>0% Broker Fees &bull; 100% Inspected</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                        Find & Book <br>
                        <span class="bg-gradient-to-r from-indigo-400 via-purple-300 to-amber-300 bg-clip-text text-transparent">Verified Stays.</span>
                    </h1>

                    <!-- Compact Fast Search Card -->
                    <div class="bg-white/95 backdrop-blur-xl p-4 sm:p-5 rounded-3xl shadow-2xl border border-white/30 text-slate-800">
                        <!-- Quick category tabs -->
                        <div class="flex items-center gap-1.5 pb-3 mb-3 border-b border-slate-100 overflow-x-auto text-[11px] font-bold">
                            <button type="button" @click="activeTab = 'all'"
                                    :class="activeTab === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap">
                                All Stays
                            </button>
                            <button type="button" @click="activeTab = 'hostel'"
                                    :class="activeTab === 'hostel' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap">
                                🛏️ Hostels (Beds)
                            </button>
                            <button type="button" @click="activeTab = 'room'"
                                    :class="activeTab === 'room' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap">
                                🚪 Private Rooms
                            </button>
                        </div>

                        <form action="{{ route('properties.search') }}" method="GET" class="space-y-3">
                            <input type="hidden" name="rent_structure" :value="activeTab === 'hostel' ? 'per_bed' : (activeTab === 'room' ? 'per_room' : '')">
                            
                            <!-- Search query -->
                            <div class="flex items-center gap-2 p-2.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus-within:border-indigo-600 focus-within:bg-white transition-all">
                                <i data-lucide="map-pin" class="w-4 h-4 text-indigo-600 flex-shrink-0"></i>
                                <input type="text" name="q" placeholder="Enter area (Ubungo, Mwenge, Sinza...)"
                                       class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 placeholder-slate-400 focus:ring-0">
                            </div>

                            <!-- Campus & Budget in 2 cols -->
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center gap-1.5 p-2 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0"></i>
                                    <select name="institution_id" class="w-full bg-transparent border-0 p-0 text-[11px] font-bold text-slate-800 focus:ring-0 cursor-pointer">
                                        <option value="">Any Campus</option>
                                        @foreach($institutions as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-1.5 p-2 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0"></i>
                                    <select name="max_price" class="w-full bg-transparent border-0 p-0 text-[11px] font-bold text-slate-800 focus:ring-0 cursor-pointer">
                                        <option value="">Any Budget</option>
                                        <option value="150000">&lt; 150k TZS</option>
                                        <option value="300000">&lt; 300k TZS</option>
                                        <option value="600000">&lt; 600k TZS</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-extrabold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all hover:scale-[1.01]">
                                <i data-lucide="search" class="w-4 h-4"></i>
                                <span>Search Accommodations</span>
                            </button>
                        </form>
                    </div>

                    <!-- Visual Student Trust Bar -->
                    <div class="flex items-center justify-center lg:justify-start gap-3 pt-1">
                        <div class="flex -space-x-2">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Student" class="w-8 h-8 rounded-full border-2 border-slate-900 object-cover">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Student" class="w-8 h-8 rounded-full border-2 border-slate-900 object-cover">
                            <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=100&q=80" alt="Student" class="w-8 h-8 rounded-full border-2 border-slate-900 object-cover">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt="Student" class="w-8 h-8 rounded-full border-2 border-slate-900 object-cover">
                        </div>
                        <div class="text-left text-xs">
                            <div class="flex items-center gap-1 text-amber-400 font-extrabold">
                                ★★★★★ <span class="text-white text-[11px] font-bold">4.9/5</span>
                            </div>
                            <span class="text-slate-400 text-[11px]">2,500+ verified student tenants</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: CLEARLY SHOWN REAL PROPERTY IMAGES SHOWCASE -->
                <div class="lg:col-span-7">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        
                        <!-- Main Big Featured Stay Photo (7 cols) -->
                        @if($heroProp)
                            <div class="sm:col-span-7 relative h-72 sm:h-96 rounded-3xl overflow-hidden shadow-2xl group border border-white/20">
                                <img src="{{ $heroProp->display_image }}" alt="{{ $heroProp->property_name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent flex flex-col justify-between p-5 text-white">
                                    <div class="flex items-center justify-between">
                                        <span class="px-3 py-1 rounded-xl bg-emerald-600/95 text-[10px] font-black uppercase tracking-wider shadow-sm flex items-center gap-1">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Verified
                                        </span>
                                        <span class="px-2.5 py-1 rounded-xl bg-slate-900/80 backdrop-blur-md text-amber-300 font-black text-xs">
                                            ★ {{ $heroProp->average_rating }}
                                        </span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-300">
                                            {{ $heroProp->propertyType->name ?? 'Student Stay' }}
                                        </span>
                                        <h3 class="text-base sm:text-lg font-black line-clamp-1">
                                            {{ $heroProp->property_name }}
                                        </h3>
                                        <div class="flex items-center justify-between pt-1">
                                            <p class="text-sm sm:text-base font-black text-white">
                                                TZS {{ number_format($heroProp->starting_price) }}
                                                <span class="text-xs font-normal text-slate-300">/{{ $heroProp->billing_period_label }}</span>
                                            </p>
                                            <a href="{{ route('properties.show', $heroProp->id) }}"
                                               class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md">
                                                Book Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Staggered 2 Smaller Real Room Photos (5 cols) -->
                        <div class="sm:col-span-5 grid grid-cols-1 gap-4">
                            @if($subHero1)
                                <a href="{{ route('properties.show', $subHero1->id) }}"
                                   class="relative h-44 sm:h-[184px] rounded-3xl overflow-hidden shadow-xl group border border-white/20 block">
                                    <img src="{{ $subHero1->display_image }}" alt="{{ $subHero1->property_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent p-4 flex flex-col justify-end text-white">
                                        <span class="text-[9px] font-extrabold uppercase text-amber-300">{{ $subHero1->district->name ?? 'Dar es Salaam' }}</span>
                                        <h4 class="text-xs font-black truncate">{{ $subHero1->property_name }}</h4>
                                        <p class="text-xs font-extrabold text-white mt-0.5">TZS {{ number_format($subHero1->starting_price) }}/{{ $subHero1->billing_period_label }}</p>
                                    </div>
                                </a>
                            @endif

                            @if($subHero2)
                                <a href="{{ route('properties.show', $subHero2->id) }}"
                                   class="relative h-44 sm:h-[184px] rounded-3xl overflow-hidden shadow-xl group border border-white/20 block">
                                    <img src="{{ $subHero2->display_image }}" alt="{{ $subHero2->property_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent p-4 flex flex-col justify-end text-white">
                                        <span class="text-[9px] font-extrabold uppercase text-emerald-400">⚡ Instant Move-in</span>
                                        <h4 class="text-xs font-black truncate">{{ $subHero2->property_name }}</h4>
                                        <p class="text-xs font-extrabold text-white mt-0.5">TZS {{ number_format($subHero2->starting_price) }}/{{ $subHero2->billing_period_label }}</p>
                                    </div>
                                </a>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 2. Visual-Card Categories (Photos First, Not Just Text) -->
    <section class="py-10 bg-white border-b border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Student Hostels -->
                <a href="{{ route('properties.search', ['rent_structure' => 'per_bed']) }}"
                   class="group relative h-36 sm:h-44 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=600&q=80" alt="Hostels"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">From 100k TZS</span>
                        <h4 class="text-sm sm:text-base font-black">Student Hostels</h4>
                    </div>
                </a>

                <!-- 2. Private Single Rooms -->
                <a href="{{ route('properties.search', ['rent_structure' => 'per_room']) }}"
                   class="group relative h-36 sm:h-44 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=600&q=80" alt="Private Rooms"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">Private Spaces</span>
                        <h4 class="text-sm sm:text-base font-black">Single Rooms</h4>
                    </div>
                </a>

                <!-- 3. Studio Apartments -->
                <a href="{{ route('properties.search', ['property_type_id' => 2]) }}"
                   class="group relative h-36 sm:h-44 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=600&q=80" alt="Studios"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">Self-Contained</span>
                        <h4 class="text-sm sm:text-base font-black">Studio Units</h4>
                    </div>
                </a>

                <!-- 4. 100% Verified Stays -->
                <a href="{{ route('properties.search', ['verified' => 1]) }}"
                   class="group relative h-36 sm:h-44 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                    <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=600&q=80" alt="Verified Stays"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-emerald-400">🛡️ Audited</span>
                        <h4 class="text-sm sm:text-base font-black">Inspected Stays</h4>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 3. Featured Accommodations (Visual Cards Grid) -->
    <section class="py-14 sm:py-16 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Top Rated</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Featured Accommodations</h2>
                </div>
                <a href="{{ route('properties.search') }}" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                    <span>View all</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- E-Commerce Product Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @forelse($featuredProperties as $prop)
                    <div class="group bg-white border border-slate-200/90 hover:border-indigo-300 rounded-3xl shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col h-full overflow-hidden relative">
                        
                        <!-- Image Container with Hover Zoom & Badges -->
                        <div class="relative h-64 bg-slate-100 overflow-hidden">
                            <img src="{{ $prop->display_image }}" alt="{{ $prop->property_name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                            <!-- Top Badges Overlay -->
                            <div class="absolute top-4 left-4 right-4 flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    @if($prop->verification_status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-600 text-white text-[10px] font-black uppercase rounded-xl shadow-md">
                                            <i data-lucide="shield-check" class="w-3 h-3"></i> Verified
                                        </span>
                                    @endif
                                </div>

                                <!-- Wishlist Heart Button -->
                                <button type="button"
                                        @click="toggleWishlist({
                                            id: {{ $prop->id }},
                                            name: '{{ addslashes($prop->property_name) }}',
                                            price: {{ $prop->starting_price }},
                                            billing: '{{ $prop->billing_period_label }}',
                                            image: '{{ $prop->display_image }}',
                                            location: '{{ addslashes($prop->district->name ?? 'Dar es Salaam') }}'
                                        })"
                                        class="p-2.5 rounded-full bg-white/90 hover:bg-white text-slate-600 hover:text-rose-600 shadow-md transition-all hover:scale-110"
                                        :class="isWishlisted({{ $prop->id }}) ? 'text-rose-600 !bg-rose-50' : 'text-slate-600'">
                                    <i data-lucide="heart" class="w-4 h-4" :class="isWishlisted({{ $prop->id }}) ? 'fill-rose-500 text-rose-500' : ''"></i>
                                </button>
                            </div>

                            <!-- Bottom Image Gradient & Distance Pill -->
                            <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-slate-950/80 to-transparent flex items-center justify-between text-white text-xs">
                                <span class="font-bold text-[11px] bg-slate-900/70 backdrop-blur-sm px-2.5 py-1 rounded-lg">
                                    <i data-lucide="map-pin" class="w-3 h-3 inline text-indigo-400"></i> {{ $prop->district->name ?? 'Dar es Salaam' }}
                                </span>
                                <span class="font-black text-xs bg-amber-400 text-slate-950 px-2 py-0.5 rounded-lg shadow-sm">
                                    ★ {{ $prop->average_rating }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-grow flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase text-indigo-600">{{ $prop->propertyType->name ?? 'Hostel' }}</span>
                                <h3 class="text-base font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1 mt-0.5">
                                    <a href="{{ route('properties.show', $prop->id) }}">{{ $prop->property_name }}</a>
                                </h3>
                                @if($prop->institution)
                                    <p class="text-xs text-slate-500 font-semibold mt-1">
                                        🎓 {{ $prop->distance_from_institution }} km from {{ $prop->institution->name }}
                                    </p>
                                @endif
                            </div>

                            <div class="border-t border-slate-100 pt-3 flex items-center justify-between">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">Starting</span>
                                    <p class="text-base font-black text-slate-950">
                                        TZS {{ number_format($prop->starting_price) }}
                                        <span class="text-xs font-normal text-slate-500">/{{ $prop->billing_period_label }}</span>
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
                                        Book Bed
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 col-span-3">No accommodations available.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 4. Campus Visual Showcase (High-Res Photos) -->
    <section class="py-14 bg-white border-y border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Locations</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">University Hubs</h2>
                </div>
                <a href="{{ route('properties.search') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                    Browse all campuses &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                <a href="{{ route('properties.search', ['q' => 'UDSM']) }}"
                   class="group relative h-48 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                    <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=600&q=80" alt="UDSM" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">Ubungo / Mlimani</span>
                        <h4 class="text-sm font-black">UDSM Campus</h4>
                    </div>
                </a>

                <a href="{{ route('properties.search', ['q' => 'Ardhi']) }}"
                   class="group relative h-48 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=600&q=80" alt="Ardhi" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">Mwenge / Survey</span>
                        <h4 class="text-sm font-black">Ardhi University</h4>
                    </div>
                </a>

                <a href="{{ route('properties.search', ['q' => 'CBE']) }}"
                   class="group relative h-48 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                    <img src="https://images.unsplash.com/photo-1519452635265-7b1fbfd1e4e0?auto=format&fit=crop&w=600&q=80" alt="CBE" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">City Center</span>
                        <h4 class="text-sm font-black">CBE & IFM</h4>
                    </div>
                </a>

                <a href="{{ route('properties.search', ['q' => 'DIT']) }}"
                   class="group relative h-48 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all">
                    <img src="https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=600&q=80" alt="DIT" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="text-[10px] font-black uppercase text-amber-300">Upanga</span>
                        <h4 class="text-sm font-black">DIT Campus</h4>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Quick View Modal (Alpine.js) -->
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
