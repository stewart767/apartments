@extends('layouts.admin')

@section('title', 'Manage Tenancy Leases')
@section('page_title', 'Lease Agreements')
@section('breadcrumb_title', 'Contracts')

@section('content')
<div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-sm font-bold text-slate-900">Lease Contracts Generated</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-650">
            <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">Contract ID</th>
                    <th class="px-6 py-4">Tenant</th>
                    <th class="px-6 py-4">Property Allocation</th>
                    <th class="px-6 py-4">Lease Term Dates</th>
                    <th class="px-6 py-4">Dues Rate</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 font-bold text-right pr-8">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($contracts as $con)
                    <tr>
                        <td class="px-6 py-4 font-bold text-slate-950">{{ $con->contract_number }}</td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $con->tenant->name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $con->tenant->phone }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-900 text-xs">{{ $con->property->property_name }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    @if($con->bed)
                                        Room {{ $con->room->room_number }} / Bed {{ $con->bed->bed_number }}
                                    @elseif($con->room)
                                        Room {{ $con->room->room_number }}
                                    @else
                                        Entire House
                                    @endif
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            {{ \Carbon\Carbon::parse($con->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($con->end_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">TZS {{ number_format($con->rent_amount) }}/{{ $con->billing_frequency }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full capitalize
                                @if($con->status === 'active') bg-emerald-50 text-emerald-700
                                @elseif($con->status === 'pending_acceptance') bg-amber-50 text-amber-700
                                @elseif($con->status === 'accepted') bg-blue-50 text-blue-700
                                @else bg-slate-50 text-slate-700 @endif">
                                {{ $con->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right pr-8 space-x-2">
                            <a href="{{ route('owner.contracts.show', $con->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-755 text-xs font-bold rounded-xl transition-all">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>View</span>
                            </a>
                            <a href="{{ route('contracts.download', $con->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                                <i data-lucide="download" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>PDF</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i data-lucide="file-signature" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                            <p class="text-sm font-semibold">No contracts generated yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($contracts->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-55/30">
            {{ $contracts->links() }}
        </div>
    @endif
</div>
@endsection
