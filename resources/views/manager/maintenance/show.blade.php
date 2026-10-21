@extends('layouts.admin')

@section('title', 'Manage Maintenance Ticket')
@section('page_title', 'Maintenance Ticket Details')
@section('breadcrumb_title', 'Ticket Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left Column: Details & Images (2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Header Info Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ticket Reference</span>
                <h3 class="text-lg font-bold text-slate-900 mt-0.5">{{ $ticket->ticket_number }}</h3>
                <p class="text-xs text-slate-500 mt-1">Property: <span class="font-semibold text-slate-800">{{ $ticket->property->property_name }}</span></p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-xs font-bold rounded-full capitalize
                    @if($ticket->status === 'resolved' || $ticket->status === 'closed') bg-emerald-50 text-emerald-700
                    @elseif($ticket->status === 'in_progress' || $ticket->status === 'assigned') bg-blue-50 text-blue-700
                    @else bg-slate-50 text-slate-700 @endif">
                    {{ $ticket->status }}
                </span>
                
                <span class="px-3 py-1 text-xs font-bold rounded-full capitalize
                    @if($ticket->priority === 'emergency') bg-rose-50 text-rose-700
                    @elseif($ticket->priority === 'high') bg-amber-50 text-amber-700
                    @else bg-slate-50 text-slate-700 @endif">
                    {{ $ticket->priority }} Priority
                </span>
            </div>
        </div>

        <!-- Issue Description -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="alert-circle" class="w-4 h-4"></i></span>
                <span>Issue Description</span>
            </h3>

            <div class="space-y-2">
                <h4 class="text-base font-bold text-slate-900">{{ $ticket->title }}</h4>
                <p class="text-sm text-slate-650 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    {{ $ticket->description }}
                </p>
            </div>
        </div>

        <!-- Attached Images -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="image" class="w-4 h-4"></i></span>
                <span>Attached Photos ({{ $ticket->images->count() }})</span>
            </h3>

            @if($ticket->images->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach($ticket->images as $img)
                        <div class="aspect-square bg-slate-100 border border-slate-200 rounded-2xl overflow-hidden hover:opacity-90 transition-opacity">
                            <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank" class="block w-full h-full">
                                <img src="{{ asset('storage/' . $img->file_path) }}" alt="Issue Photo" class="w-full h-full object-cover">
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400">No photos were uploaded for this ticket.</p>
            @endif
        </div>

        <!-- Resolution Details -->
        @if($ticket->status === 'resolved')
            <div class="bg-emerald-50/40 border border-emerald-100/50 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-emerald-900 flex items-center gap-2">
                    <span class="p-2 bg-emerald-100 text-emerald-600 rounded-xl"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
                    <span>Resolution Notes</span>
                </h3>
                <div class="text-sm text-emerald-800 leading-relaxed whitespace-pre-line bg-white/80 p-4 rounded-2xl border border-emerald-100/30">
                    {{ $ticket->resolution_notes }}
                </div>
                <p class="text-[11px] text-emerald-600">Resolved at: <span class="font-bold">{{ $ticket->resolved_at->format('M d, Y h:i A') }}</span></p>
            </div>
        @endif
    </div>

    <!-- Right Column: Operations Sidebar (1 col) -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Assignment & Tenant Info -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Tenant Details</h3>
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-sm">
                        {{ substr($ticket->tenant->name, 0, 1) }}
                    </span>
                    <div class="overflow-hidden">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $ticket->tenant->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $ticket->tenant->email }}</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-3 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                    <span>
                        @if($ticket->bed)
                            Room {{ $ticket->room->room_number }} / Bed Space {{ $ticket->bed->bed_number }}
                        @elseif($ticket->room)
                            Room {{ $ticket->room->room_number }}
                        @else
                            Entire Property
                        @endif
                    </span>
                </p>
            </div>

            <hr class="border-slate-100">

            <!-- Assign Staff Form -->
            @if($ticket->status !== 'resolved')
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Assign Staff</h3>
                    
                    @if($ticket->assignedTo)
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl flex justify-between items-center text-xs">
                            <span class="text-slate-600 font-medium">Currently: <strong class="text-slate-900">{{ $ticket->assignedTo->name }}</strong></span>
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded-md capitalize">{{ $ticket->status }}</span>
                        </div>
                    @endif

                    <form action="{{ route('manager.maintenance.assign', $ticket->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <select name="assigned_to" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-650">
                            <option value="">-- Select Manager / Owner --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ $staff->role }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-xl transition-all">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span>Assign / Update Staff</span>
                        </button>
                    </form>
                </div>

                <hr class="border-slate-100">

                <!-- Resolve Form -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mark as Resolved</h3>
                    <form action="{{ route('manager.maintenance.resolve', $ticket->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="resolution_notes" required rows="4" placeholder="Detail the repairs done (e.g. replaced bulb, plumber repaired leak)..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-650"></textarea>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-xl transition-all text-xs">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Mark Resolved</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="space-y-2 text-xs">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Assigned Handled By</span>
                    <p class="font-semibold text-slate-900">{{ $ticket->assignedTo->name ?? 'Owner/Manager Direct' }}</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
