@extends('layouts.admin')

@section('title', 'Managed Maintenance Requests')
@section('page_title', 'Maintenance Tickets')
@section('breadcrumb_title', 'Maintenance')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <span class="text-sm font-semibold text-slate-500">Manage and resolve assigned maintenance requests for your rooms</span>
    </div>

    <!-- Ticket List -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Ticket ID</th>
                        <th class="px-6 py-4">Property & Room</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Issue Title</th>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Assigned To</th>
                        <th class="px-6 py-4 font-bold text-right pr-8">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($tickets as $tick)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-950">{{ $tick->ticket_number }}</td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $tick->property->property_name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        @if($tick->bed)
                                            Room {{ $tick->room->room_number }} / Bed {{ $tick->bed->bed_number }}
                                        @elseif($tick->room)
                                            Room {{ $tick->room->room_number }}
                                        @else
                                            Entire Property
                                        @endif
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700 font-semibold">{{ $tick->tenant->name }}</td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $tick->title }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5 max-w-sm truncate">{{ $tick->description }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($tick->priority === 'emergency') bg-rose-50 text-rose-700
                                    @elseif($tick->priority === 'high') bg-amber-50 text-amber-700
                                    @else bg-slate-50 text-slate-700 @endif">
                                    {{ $tick->priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                    @if($tick->status === 'resolved' || $tick->status === 'closed') bg-emerald-50 text-emerald-700
                                    @elseif($tick->status === 'in_progress') bg-blue-50 text-blue-700
                                    @else bg-slate-50 text-slate-700 @endif">
                                    {{ $tick->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">{{ $tick->assignedTo->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-4 text-right pr-8">
                                <a href="{{ route('manager.maintenance.show', $tick->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-750 text-xs font-bold rounded-xl transition-all">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Manage</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No maintenance tickets received.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-55/30">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
