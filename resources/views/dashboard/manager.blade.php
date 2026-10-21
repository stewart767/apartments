@extends('layouts.admin')

@section('title', 'Manager Dashboard')
@section('page_title', 'Hostel Management Overview')
@section('breadcrumb_title', 'Overview')

@section('content')
<div class="space-y-8">
    
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1 -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <i data-lucide="building" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Assigned Properties</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['assigned_properties'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    Occupancy Rate: <span class="text-indigo-600 font-bold">{{ $stats['occupancy_rate'] }}%</span>
                </span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i data-lucide="users" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Tenants</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_tenants'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    <span class="text-emerald-600 font-semibold">{{ $stats['available_beds'] }}</span> beds,
                    <span class="text-indigo-600 font-semibold">{{ $stats['available_rooms'] }}</span> rooms free
                </span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Bookings</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['pending_applications'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">Awaiting response</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i data-lucide="wrench" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Maintenance</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['maintenance_requests'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">Active request tickets</span>
            </div>
        </div>
    </div>

    <!-- Quick Check-in/Check-out operations panel -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Reception Actions Panel -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm shadow-slate-100/50 lg:col-span-1">
            <h4 class="text-sm font-bold text-slate-900 mb-4">Front Desk Quick Operations</h4>
            <div class="space-y-3">
                <a href="{{ route('manager.checkins') }}" class="flex items-center gap-3 p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all">
                    <span class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="door-open" class="w-4 h-4"></i></span>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-slate-700">Check-In Tenant</p>
                        <p class="text-[10px] text-slate-400">Allocate room, key handover</p>
                    </div>
                </a>

                <a href="{{ route('manager.checkins', ['checkout' => 1]) }}" class="flex items-center gap-3 p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all">
                    <span class="p-2.5 bg-rose-50 text-rose-600 rounded-lg"><i data-lucide="log-out" class="w-4 h-4"></i></span>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-slate-700">Check-Out Tenant</p>
                        <p class="text-[10px] text-slate-400">Log damages, free beds</p>
                    </div>
                </a>

                <a href="{{ route('manager.contracts') }}" class="flex items-center gap-3 p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all">
                    <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg"><i data-lucide="file-signature" class="w-4 h-4"></i></span>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-slate-700">Review Contracts</p>
                        <p class="text-[10px] text-slate-400">Check active & expiring lease agreements</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Booking Applications Awaiting Action -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden lg:col-span-2">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Recent Booking Applications</h4>
                <a href="{{ route('manager.applications') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentApplications as $app)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $app->tenant->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Applied for {{ $app->property->property_name }} &bull; TZS {{ number_format($app->price) }} / {{ $app->billing_period }}</p>
                        </div>
                        <a href="{{ route('manager.applications.show', $app->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-all">Review</a>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">
                        <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No pending applications.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
