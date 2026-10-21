@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')
@section('page_title', 'System Overview')
@section('breadcrumb_title', 'Overview')

@section('content')
<div class="space-y-8">
    
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1 -->
        <div class="bg-white overflow-hidden border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <i data-lucide="building-2" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Properties</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_properties'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    <span class="text-indigo-600 font-semibold">{{ $stats['verified_properties'] }}</span> verified,
                    <span class="text-amber-500 font-semibold">{{ $stats['pending_properties'] }}</span> pending
                </span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white overflow-hidden border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i data-lucide="users" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">System Users</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_owners'] + $stats['total_tenants'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    <span class="text-emerald-600 font-semibold">{{ $stats['total_owners'] }}</span> owners,
                    <span class="text-slate-600 font-semibold">{{ $stats['total_tenants'] }}</span> tenants
                </span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white overflow-hidden border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <i data-lucide="file-signature" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Tenancies</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['active_tenancies'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    <span class="text-rose-500 font-semibold">{{ $stats['expiring_contracts'] }}</span> expiring soon
                </span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white overflow-hidden border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i data-lucide="coins" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Monthly Revenue</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">TZS {{ number_format($stats['monthly_revenue']) }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    <span class="text-rose-500 font-semibold">TZS {{ number_format($stats['outstanding_payments']) }}</span> outstanding
                </span>
            </div>
        </div>
    </div>

    <!-- Secondary stats overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Room Status -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm shadow-slate-100/50">
            <h4 class="text-sm font-bold text-slate-900 mb-4">Room & Capacity Allocation</h4>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs text-slate-500 font-medium mb-1">
                        <span>Available Beds</span>
                        <span class="font-bold text-emerald-600">{{ $stats['available_beds'] }} beds</span>
                    </div>
                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                        @php
                            $totalBedsCount = $stats['available_beds'] + $stats['occupied_beds'];
                            $pct = $totalBedsCount > 0 ? ($stats['occupied_beds'] / $totalBedsCount) * 100 : 0;
                        @endphp
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ 100 - $pct }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-500 font-medium mb-1">
                        <span>Occupied Beds</span>
                        <span class="font-bold text-indigo-600">{{ $stats['occupied_beds'] }} beds</span>
                    </div>
                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                <div class="flex justify-between border-t border-slate-50 pt-4 text-xs font-semibold text-slate-700">
                    <span>Total Active Rooms</span>
                    <span>{{ $stats['total_rooms'] }}</span>
                </div>
            </div>
        </div>

        <!-- Pending Items Summary -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm shadow-slate-100/50">
            <h4 class="text-sm font-bold text-slate-900 mb-4">Pending Approvals Queue</h4>
            <div class="space-y-3.5">
                <div class="flex items-center justify-between p-3 bg-amber-50/50 border border-amber-100/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="p-2 bg-amber-50 text-amber-600 rounded-lg"><i data-lucide="building" class="w-4 h-4"></i></span>
                        <span class="text-sm font-semibold text-slate-700">Property Listings</span>
                    </div>
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">{{ $stats['pending_properties'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-blue-50/50 border border-blue-100/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i data-lucide="clipboard-list" class="w-4 h-4"></i></span>
                        <span class="text-sm font-semibold text-slate-700">Tenancy Bookings</span>
                    </div>
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">{{ $stats['pending_applications'] }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-rose-50/50 border border-rose-100/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <span class="p-2 bg-rose-50 text-rose-600 rounded-lg"><i data-lucide="wrench" class="w-4 h-4"></i></span>
                        <span class="text-sm font-semibold text-slate-700">Maintenance Request</span>
                    </div>
                    <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">{{ $stats['maintenance_requests'] }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Admin Tools -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm shadow-slate-100/50">
            <h4 class="text-sm font-bold text-slate-900 mb-4">Quick Management Actions</h4>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.properties') }}" class="flex flex-col items-center justify-center p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all text-center">
                    <i data-lucide="shield-check" class="w-5 h-5 text-indigo-500 mb-2"></i>
                    <span class="text-xs font-semibold text-slate-700">Verify Properties</span>
                </a>
                <a href="{{ route('admin.users') }}" class="flex flex-col items-center justify-center p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all text-center">
                    <i data-lucide="user-plus" class="w-5 h-5 text-emerald-500 mb-2"></i>
                    <span class="text-xs font-semibold text-slate-700">Manage Users</span>
                </a>
                <a href="{{ route('admin.amenities') }}" class="flex flex-col items-center justify-center p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all text-center">
                    <i data-lucide="sparkles" class="w-5 h-5 text-amber-500 mb-2"></i>
                    <span class="text-xs font-semibold text-slate-700">Setup Amenities</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all text-center">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-500 mb-2"></i>
                    <span class="text-xs font-semibold text-slate-700">Finance Reports</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Data Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Properties -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Recently Registered Properties</h4>
                <a href="{{ route('admin.properties') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentProperties as $prop)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $prop->property_name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Owned by {{ $prop->owner->name }} &bull; {{ $prop->propertyType->name }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                            @if($prop->verification_status === 'approved') bg-emerald-50 text-emerald-700
                            @elseif($prop->verification_status === 'pending') bg-amber-50 text-amber-700
                            @else bg-rose-50 text-rose-700 @endif capitalize">
                            {{ $prop->verification_status }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">
                        <i data-lucide="building" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No registered properties yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Recent Booking Applications</h4>
                <a href="{{ route('admin.applications') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentApplications as $app)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $app->tenant->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Applied for {{ $app->property->property_name }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                            @if($app->status === 'approved') bg-emerald-50 text-emerald-700
                            @elseif($app->status === 'pending') bg-blue-50 text-blue-700
                            @else bg-slate-50 text-slate-700 @endif capitalize">
                            {{ $app->status }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">
                        <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No booking applications yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
