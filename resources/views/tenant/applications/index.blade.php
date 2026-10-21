@extends('layouts.admin')

@section('title', 'My Booking Applications')
@section('page_title', 'My Applications')
@section('breadcrumb_title', 'Applications')

@section('content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-sm font-bold text-slate-900">Application Queue</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-650">
            <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">App Number</th>
                    <th class="px-6 py-4">Property</th>
                    <th class="px-6 py-4">Allocation</th>
                    <th class="px-6 py-4">Rent Price</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Submitted Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4 font-bold text-slate-950">{{ $app->application_number }}</td>
                        <td class="px-6 py-4">{{ $app->property->property_name }}</td>
                        <td class="px-6 py-4">
                            @if($app->bed)
                                Room {{ $app->room->room_number }} / Bed {{ $app->bed->bed_number }}
                            @elseif($app->room)
                                Room {{ $app->room->room_number }}
                            @else
                                Entire Property
                            @endif
                        </td>
                        <td class="px-6 py-4">TZS {{ number_format($app->price) }}/{{ $app->billing_period }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                @if($app->status === 'approved') bg-emerald-50 text-emerald-700
                                @elseif($app->status === 'pending') bg-blue-50 text-blue-700
                                @elseif($app->status === 'under_review') bg-amber-50 text-amber-700
                                @else bg-slate-50 text-slate-700 @endif">
                                {{ $app->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-xs">{{ $app->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i data-lucide="clipboard-x" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                            <p class="text-sm font-semibold">You haven't submitted any booking applications yet.</p>
                            <a href="{{ route('properties.search') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-indigo-650 hover:underline">Find a place now</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($applications->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-55/30">
            {{ $applications->links() }}
        </div>
    @endif
</div>
@endsection
