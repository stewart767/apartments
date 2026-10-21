@extends('layouts.property_dashboard')

@section('property_content')
<!-- Overview statistics cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Occupancy rate -->
    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Occupancy Rate</span>
            <i data-lucide="percent" class="w-5 h-5"></i>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="text-2xl font-bold text-slate-900">{{ $occupancyRate }}%</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Calculated at rooms/beds level</p>
    </div>

    <!-- Active Rooms / Beds count -->
    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Rooms & Beds</span>
            <i data-lucide="door-open" class="w-5 h-5"></i>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="text-2xl font-bold text-slate-900">{{ $totalRooms }} / {{ $totalBeds }}</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Available beds: {{ $availableBeds }}</p>
    </div>

    <!-- Expected Revenue -->
    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Expected Revenue</span>
            <i data-lucide="wallet" class="w-5 h-5"></i>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="text-xl font-bold text-slate-900">TZS {{ number_format($expectedRevenue) }}</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Total invoiced amount</p>
    </div>

    <!-- Collected Revenue -->
    <div class="bg-white border border-slate-100 p-6 rounded-3xl shadow-sm shadow-slate-100/50">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Collected Revenue</span>
            <i data-lucide="banknote" class="w-5 h-5"></i>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="text-xl font-bold text-slate-900">TZS {{ number_format($collectedRevenue) }}</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Outstanding: TZS {{ number_format($outstandingRevenue) }}</p>
    </div>
</div>

<!-- Grid section: Bookings vs Maintenance -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Contracts / Tenancies -->
    <div class="bg-white border border-slate-100 p-6 sm:p-8 rounded-3xl shadow-sm shadow-slate-100/50">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i data-lucide="file-signature" class="w-4 h-4 text-indigo-650"></i>
            <span>Active Tenancies</span>
        </h3>
        @if($recentContracts->isEmpty())
            <div class="text-center py-10 text-slate-400 text-xs">
                <p>No active tenants yet.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($recentContracts as $contract)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">{{ $contract->tenant->name }}</p>
                            <p class="text-slate-500 mt-0.5">Room: {{ $contract->room->room_number ?? 'N/A' }} | Bed: {{ $contract->bed->bed_number ?? 'N/A' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold capitalize">{{ $contract->status }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recent Maintenance Tickets -->
    <div class="bg-white border border-slate-100 p-6 sm:p-8 rounded-3xl shadow-sm shadow-slate-100/50">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i data-lucide="wrench" class="w-4 h-4 text-indigo-650"></i>
            <span>Maintenance Tickets</span>
        </h3>
        @if($recentMaintenance->isEmpty())
            <div class="text-center py-10 text-slate-400 text-xs">
                <p>No open tickets reported.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($recentMaintenance as $ticket)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">{{ $ticket->title }}</p>
                            <p class="text-slate-500 mt-0.5">Room: {{ $ticket->room->room_number ?? 'N/A' }} | Priority: <span class="font-semibold capitalize text-amber-700">{{ $ticket->priority }}</span></p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold capitalize">{{ $ticket->status }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
