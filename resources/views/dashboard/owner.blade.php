@extends('layouts.admin')

@section('title', 'Owner Dashboard')
@section('page_title', 'Property Owner Overview')
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
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">My Properties</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_properties'] }}</h3>
                <span class="text-[10px] text-slate-500 font-semibold block leading-normal">
                    Occupancy Rate: <span class="text-indigo-600 font-bold">{{ $stats['occupancy_rate'] }}%</span>
                </span>
                <span class="text-[10px] text-slate-400 font-medium block leading-normal">
                    {{ $stats['total_buildings'] ?? 0 }} Buildings | {{ $stats['total_floors'] ?? 0 }} Floors
                </span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i data-lucide="users-round" class="w-5 h-5"></i>
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
                <i data-lucide="coins" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Revenue</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">TZS {{ number_format($stats['total_revenue']) }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    <span class="text-rose-500 font-semibold">TZS {{ number_format($stats['outstanding_payments']) }}</span> outstanding
                </span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <i data-lucide="file-clock" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Contract Expirations</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['expiring_contracts'] }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">Expiring in 30 days</span>
            </div>
        </div>
    </div>

    <!-- Quick Links & Actions Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions Panel -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm shadow-slate-100/50 lg:col-span-1">
            <h4 class="text-sm font-bold text-slate-900 mb-4">Quick Management Actions</h4>
            <div class="space-y-3">
                <a href="{{ route('owner.properties.wizard') }}" class="flex items-center gap-3 p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all">
                    <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg"><i data-lucide="plus" class="w-4 h-4"></i></span>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-slate-700">Add New Property</p>
                        <p class="text-[10px] text-slate-400">List a hostel or apartment</p>
                    </div>
                </a>

                <a href="{{ route('owner.applications') }}" class="flex items-center justify-between p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-blue-50 text-blue-600 rounded-lg"><i data-lucide="clipboard-list" class="w-4 h-4"></i></span>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-slate-700">Review Applications</p>
                            <p class="text-[10px] text-slate-400">Approve or reject bookings</p>
                        </div>
                    </div>
                    @if($stats['pending_applications'] > 0)
                        <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">{{ $stats['pending_applications'] }}</span>
                    @endif
                </a>

                <a href="{{ route('owner.payments') }}" class="flex items-center gap-3 p-3 border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 rounded-xl transition-all">
                    <span class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="banknote" class="w-4 h-4"></i></span>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-slate-700">Record Payments</p>
                        <p class="text-[10px] text-slate-400">Track manual cash or transfer rent</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activities / Logs -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden lg:col-span-2">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Pending Booking Applications</h4>
                <a href="{{ route('owner.applications') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Review all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentApplications as $app)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $app->tenant->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Applied for {{ $app->property->property_name }} &bull; TZS {{ number_format($app->price) }} / {{ $app->billing_period }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('owner.applications.show', $app->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-all">Review</a>
                        </div>
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

    <!-- Downward Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Maintenance Requests -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Active Maintenance Requests</h4>
                <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">{{ $stats['maintenance_requests'] }} requests</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentMaintenance as $req)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $req->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Property: {{ $req->property->property_name }} &bull; Tenant: {{ $req->tenant->name }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                            @if($req->priority === 'emergency') bg-rose-50 text-rose-700
                            @elseif($req->priority === 'high') bg-amber-50 text-amber-700
                            @else bg-slate-50 text-slate-700 @endif capitalize">
                            {{ $req->priority }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">
                        <i data-lucide="wrench" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No active maintenance requests.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Payments Log -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Recent Payment Audits</h4>
                <a href="{{ route('owner.payments') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View ledger</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentPayments as $pay)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $pay->tenant->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">TZS {{ number_format($pay->amount) }} &bull; Type: {{ $pay->payment_type }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                            @if($pay->status === 'paid') bg-emerald-50 text-emerald-700
                            @elseif($pay->status === 'pending') bg-amber-50 text-amber-700
                            @else bg-rose-50 text-rose-700 @endif capitalize">
                            {{ $pay->status }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">
                        <i data-lucide="banknote" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No payment records found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
