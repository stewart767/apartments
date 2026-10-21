@extends('layouts.admin')

@section('title', 'My Maintenance Tickets')
@section('page_title', 'Maintenance Repair Requests')
@section('breadcrumb_title', 'Maintenance')

@section('content')
<div class="space-y-6">

    <!-- Actions bar -->
    <div class="flex justify-between items-center">
        <span class="text-sm font-semibold text-slate-500">Track and report room maintenance issues</span>
        <a href="{{ route('tenant.maintenance.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-100">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>File Repair Ticket</span>
        </a>
    </div>

    <!-- Ticket List -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-650">
                <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Ticket ID</th>
                        <th class="px-6 py-4">Issue Title</th>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Filed Date</th>
                        <th class="px-6 py-4">Assigned To</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($tickets as $tick)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-950">{{ $tick->ticket_number }}</td>
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
                            <td class="px-6 py-4 text-slate-400 text-xs">{{ $tick->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700">{{ $tick->assignedTo->name ?? 'Awaiting dispatch' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold">No maintenance tickets submitted.</p>
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
