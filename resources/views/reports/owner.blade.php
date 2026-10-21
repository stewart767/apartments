@extends('layouts.admin')

@section('title', 'Property Reports & Analytics')
@section('page_title', 'Performance Reports')
@section('breadcrumb_title', 'Reports')

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
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Revenue Collected</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">TZS {{ number_format($data['total_revenue']) }}</h3>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Calculated from all verified paid payments</p>
        </div>

        <!-- Outstanding Payments Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <span class="p-2 bg-rose-50 text-rose-650 rounded-xl inline-flex mb-4">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </span>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Outstanding Rent/Deposits</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">TZS {{ number_format($data['outstanding_balance']) }}</h3>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Unpaid or overdue invoice balances</p>
        </div>

        <!-- Occupancy Rate Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <span class="p-2 bg-emerald-50 text-emerald-650 rounded-xl inline-flex mb-4">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </span>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Bed/Room Occupancy Rate</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-2xl font-extrabold text-slate-900">{{ $data['occupancy_rate'] }}%</h3>
                    <span class="text-xs text-slate-500 font-semibold">({{ $data['occupied_beds'] }} / {{ $data['total_beds'] }} beds)</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $data['occupancy_rate'] }}%"></div>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-4">Active leases relative to capacity</p>
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
            <p class="text-[10px] text-slate-400 mt-4">Unresolved requests currently assigned</p>
        </div>
    </div>

    <!-- Revenue Breakdown & Priorities Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Revenue by Property (2 cols) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900">Revenue Collections by Property</h3>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm text-slate-650">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Property Name</th>
                            <th class="px-6 py-4 text-right pr-8">Total Earnings</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($data['revenue_by_property'] as $prop)
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $prop['name'] }}</td>
                                <td class="px-6 py-4 text-right pr-8 text-indigo-600 font-extrabold">TZS {{ number_format($prop['revenue']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-8 text-center text-slate-400">No active properties or payments.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Maintenance Priorities (1 col) -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col">
            <h3 class="text-sm font-bold text-slate-900 mb-6 pb-2 border-b border-slate-100">Tickets by Priority Level</h3>
            <div class="space-y-4 flex-grow flex flex-col justify-center">
                @php
                    $priorities = ['emergency', 'high', 'medium', 'low'];
                @endphp
                @foreach($priorities as $pri)
                    @php
                        $count = $data['priority_counts'][$pri] ?? 0;
                        $total = array_sum($data['priority_counts']);
                        $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-semibold text-slate-700 capitalize">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full 
                                    @if($pri === 'emergency') bg-rose-500
                                    @elseif($pri === 'high') bg-amber-500
                                    @elseif($pri === 'medium') bg-blue-500
                                    @else bg-slate-400 @endif"></span>
                                <span>{{ $pri }}</span>
                            </span>
                            <span>{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="w-full bg-slate-50 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full rounded-full 
                                @if($pri === 'emergency') bg-rose-500
                                @elseif($pri === 'high') bg-amber-500
                                @elseif($pri === 'medium') bg-blue-500
                                @else bg-slate-400 @endif" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
