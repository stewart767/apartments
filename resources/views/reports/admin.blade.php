@extends('layouts.admin')

@section('title', 'System Reports & Global Analytics')
@section('page_title', 'System Administration Performance')
@section('breadcrumb_title', 'System Reports')

@section('content')
<div class="space-y-8">
    
    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl inline-flex mb-4">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </span>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">System Total Revenue</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">TZS {{ number_format($data['total_revenue']) }}</h3>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Verified rent and deposit payments collections</p>
        </div>

        <!-- Outstanding Payments Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <span class="p-2 bg-rose-50 text-rose-650 rounded-xl inline-flex mb-4">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </span>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Outstanding System Dues</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">TZS {{ number_format($data['outstanding_balance']) }}</h3>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Total unpaid/overdue balance globally</p>
        </div>

        <!-- Occupancy Rate Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <span class="p-2 bg-emerald-50 text-emerald-650 rounded-xl inline-flex mb-4">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </span>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Global Bed Occupancy</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-extrabold text-slate-900">{{ $data['occupancy_rate'] }}%</h3>
                    <span class="text-xs text-slate-500 font-semibold">({{ $data['occupied_beds'] }} / {{ $data['total_beds'] }} beds)</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $data['occupancy_rate'] }}%"></div>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Active occupancies across all properties</p>
        </div>

        <!-- Maintenance Load Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <span class="p-2 bg-amber-50 text-amber-650 rounded-xl inline-flex mb-4">
                    <i data-lucide="wrench" class="w-5 h-5"></i>
                </span>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Maintenance Ticket Ratio</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-extrabold text-slate-900">
                        {{ $data['open_tickets'] }} <span class="text-xs text-slate-400 font-medium">open</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-semibold">/ {{ $data['resolved_tickets'] }} resolved</span>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Unresolved global tickets ratio</p>
        </div>
    </div>

    <!-- Properties Verification & Roles Breakdowns Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- User Accounts Roles Distribution -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col">
            <h3 class="text-sm font-bold text-slate-900 mb-6 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4 text-indigo-650"></i>
                <span>Registered Users Distribution</span>
            </h3>
            <div class="space-y-4">
                @php
                    $roles = ['superadmin', 'owner', 'manager', 'receptionist', 'agent', 'tenant'];
                @endphp
                @foreach($roles as $role)
                    @php
                        $count = $data['user_roles'][$role] ?? 0;
                        $total = array_sum($data['user_roles']);
                        $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-semibold text-slate-700 capitalize">
                            <span>{{ $role }}</span>
                            <span>{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="w-full bg-slate-50 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Property Verification Status Breakdown -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col">
            <h3 class="text-sm font-bold text-slate-900 mb-6 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <i data-lucide="building" class="w-4 h-4 text-indigo-650"></i>
                <span>Properties Verification Breakdown</span>
            </h3>
            <div class="space-y-4">
                @php
                    $statuses = ['approved', 'pending', 'rejected', 'changes_requested'];
                @endphp
                @foreach($statuses as $st)
                    @php
                        $count = $data['property_status'][$st] ?? 0;
                        $total = array_sum($data['property_status']);
                        $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-semibold text-slate-700 capitalize">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full 
                                    @if($st === 'approved') bg-emerald-500
                                    @elseif($st === 'pending') bg-amber-500
                                    @elseif($st === 'rejected') bg-rose-500
                                    @else bg-slate-400 @endif"></span>
                                <span>
                                    @if($st === 'approved') Verified Approved
                                    @elseif($st === 'pending') Pending Verification
                                    @elseif($st === 'rejected') Rejected
                                    @else Changes Requested @endif
                                </span>
                            </span>
                            <span>{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="w-full bg-slate-50 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full 
                                @if($st === 'approved') bg-emerald-500
                                @elseif($st === 'pending') bg-amber-500
                                @elseif($st === 'rejected') bg-rose-500
                                @else bg-slate-400 @endif" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
