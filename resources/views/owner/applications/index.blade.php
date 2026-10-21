@extends('layouts.admin')

@section('title', 'Booking Applications Manager')
@section('page_title', 'Applications Received')
@section('breadcrumb_title', 'Applications')

@section('content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-sm font-bold text-slate-900">Submitted Booking Queues</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-650">
            <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">App Number</th>
                    <th class="px-6 py-4">Applicant</th>
                    <th class="px-6 py-4">Property</th>
                    <th class="px-6 py-4">Allocated Room/Bed</th>
                    <th class="px-6 py-4">Dues Quote</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4 font-bold text-slate-950">{{ $app->application_number }}</td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $app->tenant->name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $app->tenant->phone }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ $app->property->property_name }}</td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                            @if($app->bed)
                                Room {{ $app->room->room_number }} / Bed {{ $app->bed->bed_number }}
                            @elseif($app->room)
                                Room {{ $app->room->room_number }}
                            @else
                                Entire Unit
                            @endif
                        </td>
                        <td class="px-6 py-4">TZS {{ number_format($app->price) }}/{{ $app->billing_period }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                @if($app->status === 'approved') bg-emerald-50 text-emerald-700
                                @elseif($app->status === 'pending') bg-blue-50 text-blue-700
                                @elseif($app->status === 'under_review') bg-amber-50 text-amber-700
                                @else bg-rose-50 text-rose-700 @endif">
                                {{ $app->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('owner.applications.show', $app->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-750 text-xs font-bold rounded-xl transition-all">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Review</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                            <p class="text-sm font-semibold">No booking applications received yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applications->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
