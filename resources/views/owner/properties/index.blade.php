@extends('layouts.admin')

@section('title', 'My Properties')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between border-b border-slate-200 pb-6 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Properties</h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage your student hostels, apartments, and co-living houses.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('owner.properties.wizard') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Property</span>
            </a>
        </div>
    </div>

    <!-- Properties Grid -->
    @if($properties->isEmpty())
        <!-- Empty State -->
        <div class="text-center py-20 bg-white border border-slate-100 rounded-3xl shadow-sm shadow-slate-100/50">
            <div class="inline-flex p-4 bg-indigo-50 text-indigo-650 rounded-2xl mb-4">
                <i data-lucide="building" class="w-10 h-10"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">No properties listed yet</h3>
            <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
                Get started by listing your first student hostel, apartment or bedsitter to start receiving bookings.
            </p>
            <div class="mt-6">
                <a href="{{ route('owner.properties.wizard') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>List First Property</span>
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($properties as $property)
                @php
                    $totalCapacity = 0;
                    $totalOccupancy = 0;
                    foreach($property->rooms as $room) {
                        $totalCapacity += $room->capacity;
                        $totalOccupancy += $room->occupancy;
                    }
                    
                    if ($totalCapacity === 0 || $totalOccupancy === 0) {
                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                        $dotClass = 'bg-emerald-500';
                        $statusText = 'Available';
                    } elseif ($totalOccupancy >= $totalCapacity) {
                        $badgeClass = 'bg-rose-50 text-rose-700 border-rose-100';
                        $dotClass = 'bg-rose-500';
                        $statusText = 'Full';
                    } else {
                        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-100';
                        $dotClass = 'bg-amber-500';
                        $statusText = 'Partial';
                    }
                @endphp
                <div class="group relative bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col justify-between">
                    <!-- Top section -->
                    <div>
                        <!-- Property primary image or gradient fallback -->
                        <div class="h-40 relative flex items-center justify-center border-b border-slate-100 bg-slate-50 overflow-hidden">
                            @if($property->primaryImage)
                                <img src="{{ asset('storage/' . $property->primaryImage->file_path) }}" alt="{{ $property->property_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-slate-50 flex items-center justify-center">
                                    <span class="p-3 bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-slate-100 text-indigo-600">
                                        <i data-lucide="building" class="w-6 h-6"></i>
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Badges -->
                            <div class="absolute top-4 left-4 flex flex-col gap-1.5">
                                @if($property->verification_status === 'approved')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Verified
                                    </span>
                                @elseif($property->verification_status === 'pending')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 border border-amber-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pending Audit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 border border-rose-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Rejected
                                    </span>
                                @endif
                            </div>

                            <!-- Occupancy Badge -->
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold border {{ $badgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">{{ $property->propertyType->name }}</span>
                                <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $property->property_name }}</h3>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ $property->ward->name }}, {{ $property->district->name }}</span>
                                </p>
                            </div>

                            <p class="text-sm text-slate-650 line-clamp-2">{{ $property->description }}</p>

                            <!-- Financial Summary -->
                            <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-xs text-slate-500">
                                <div>
                                    <p class="font-semibold text-slate-900 text-sm">
                                        TZS {{ number_format($property->price ?? 0) }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Base price / {{ $property->billing_period ?? 'monthly' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-800 uppercase tracking-wider text-[10px]">
                                        Rent Type
                                    </p>
                                    <p class="text-slate-500 mt-0.5">{{ str_replace('_', ' ', $property->rent_structure) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="p-6 pt-0 mt-4 border-t border-slate-50 flex items-center gap-3">
                        <span class="text-xs text-slate-400">Listing Status: <span class="font-semibold text-slate-700 capitalize">{{ $property->status }}</span></span>
                        <div class="ml-auto flex items-center gap-2">
                            <a href="{{ route('owner.properties.edit', $property->id) }}" class="inline-flex items-center justify-center p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-50 transition-colors" title="Edit details">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
