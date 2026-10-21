@extends('layouts.admin')

@section('title', 'Tenant Dashboard')
@section('page_title', 'My Tenancy')
@section('breadcrumb_title', 'Overview')

@section('content')
<div class="space-y-8">
    
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Active Lease -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <i data-lucide="key" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lease Status</p>
                <h3 class="text-lg font-bold text-slate-900 mt-1">
                    {{ $activeContract ? 'Active Tenancy' : 'No Active Lease' }}
                </h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    @if($activeContract)
                        Room {{ $activeContract->room->room_number ?? 'N/A' }}
                    @else
                        Browse accommodation to apply
                    @endif
                </span>
            </div>
        </div>

        <!-- Card 2: Rent Cost -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Rent Cost</p>
                <h3 class="text-lg font-bold text-slate-900 mt-1">
                    @if($activeContract)
                        TZS {{ number_format($activeContract->rent_amount) }}
                    @else
                        TZS 0
                    @endif
                </h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    @if($activeContract)
                        per {{ $activeContract->billing_frequency }}
                    @else
                        No rent obligations
                    @endif
                </span>
            </div>
        </div>

        <!-- Card 3: Next Payment -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Next Rent Due</p>
                <h3 class="text-lg font-bold text-slate-900 mt-1">
                    @if($nextPayment)
                        TZS {{ number_format($nextPayment->amount) }}
                    @else
                        Settled
                    @endif
                </h3>
                <span class="text-[11px] text-slate-500 font-medium">
                    @if($nextPayment && $nextPayment->due_date)
                        Due by: <span class="text-rose-500 font-bold">{{ \Carbon\Carbon::parse($nextPayment->due_date)->format('d M, Y') }}</span>
                    @else
                        No pending invoice
                    @endif
                </span>
            </div>
        </div>

        <!-- Card 4: Action Requests -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm shadow-slate-100/50">
            <span class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <i data-lucide="wrench" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Tickets</p>
                <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $openMaintenanceCount }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">Pending repairs</span>
            </div>
        </div>
    </div>

    <!-- Active Property Information -->
    @if($activeContract)
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm shadow-slate-100/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <span class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl"><i data-lucide="building-2" class="w-6 h-6"></i></span>
                <div>
                    <h4 class="text-base font-bold text-slate-950">{{ $activeContract->property->property_name }}</h4>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $activeContract->property->address }}</p>
                    <div class="flex flex-wrap gap-4 mt-3 text-xs font-medium text-slate-500">
                        @if($activeContract->building)
                            <span class="flex items-center gap-1.5"><i data-lucide="box" class="w-3.5 h-3.5"></i> Building: {{ $activeContract->building->building_name }}</span>
                        @endif
                        @if($activeContract->floor)
                            <span class="flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5"></i> Floor: {{ $activeContract->floor->floor_number_name }}</span>
                        @endif
                        <span class="flex items-center gap-1.5"><i data-lucide="door-closed" class="w-3.5 h-3.5"></i> Room Number: {{ $activeContract->room->room_number }}</span>
                        @if($activeContract->bed)
                            <span class="flex items-center gap-1.5"><i data-lucide="bed" class="w-3.5 h-3.5"></i> Bed Number: {{ $activeContract->bed->bed_number }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-100 md:border-t-0 pt-4 md:pt-0 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('tenant.contracts.show', $activeContract->id) }}" class="inline-flex justify-center items-center gap-1.5 px-4 py-2 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Lease Contract</span>
                </a>
                <a href="{{ route('tenant.maintenance.create') }}" class="inline-flex justify-center items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-100">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Submit Repair Request</span>
                </a>
            </div>
        </div>
    @else
        <!-- Empty Tenancy Banner -->
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-3xl p-8 text-center max-w-2xl mx-auto space-y-4">
            <span class="inline-flex p-4 bg-indigo-100/50 text-indigo-600 rounded-2xl"><i data-lucide="search" class="w-8 h-8"></i></span>
            <h4 class="text-lg font-bold text-slate-900">Find your next home or room!</h4>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">
                You do not have any active room reservation. Browse list of verified student hostels, apartments and houses near institutions.
            </p>
            <div class="pt-2">
                <a href="{{ route('properties.search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-100">
                    <span>Find Accommodation</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    @endif

    <!-- Data Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Rent Payments History -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Payment Invoice Receipts</h4>
                <a href="{{ route('tenant.payments') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($paymentsHistory as $pay)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">TZS {{ number_format($pay->amount) }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Reference: {{ $pay->payment_reference }} &bull; Type: {{ $pay->payment_type }}</p>
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
                        <i data-lucide="wallet" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No transactions recorded.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Maintenance Ticket Requests -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm shadow-slate-100/50 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Recent Maintenance Tickets</h4>
                <a href="{{ route('tenant.maintenance') }}" class="text-xs text-indigo-600 font-semibold hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($maintenanceRequests as $req)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $req->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Ticket: {{ $req->ticket_number }} &bull; Date: {{ $req->created_at->format('d M, Y') }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                            @if($req->status === 'resolved' || $req->status === 'closed') bg-emerald-50 text-emerald-700
                            @elseif($req->status === 'in_progress') bg-blue-50 text-blue-700
                            @else bg-slate-50 text-slate-700 @endif capitalize">
                            {{ $req->status }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400">
                        <i data-lucide="wrench" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">No maintenance tickets submitted.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
