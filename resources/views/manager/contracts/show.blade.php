@extends('layouts.admin')

@section('title', 'Review Tenancy Agreement')
@section('page_title', 'Lease Contract Terms')
@section('breadcrumb_title', 'Agreement')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Details Panel (Left 2 cols) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Header Info Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lease Reference</span>
                <h3 class="text-lg font-bold text-slate-900 mt-0.5">{{ $contract->contract_number }}</h3>
                <p class="text-xs text-slate-500 mt-1">Tenant: <span class="font-semibold text-slate-800">{{ $contract->tenant->name }}</span></p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-xs font-bold rounded-full capitalize
                    @if($contract->status === 'active') bg-emerald-50 text-emerald-700
                    @elseif($contract->status === 'pending_acceptance') bg-amber-50 text-amber-700
                    @elseif($contract->status === 'accepted') bg-blue-50 text-blue-700
                    @else bg-slate-50 text-slate-700 @endif">
                    {{ $contract->status }}
                </span>
                
                <a href="{{ route('contracts.download', $contract->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all border border-slate-200">
                    <i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
                    <span>Download PDF</span>
                </a>
            </div>
        </div>

        <!-- Lease Specifications -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="info" class="w-4 h-4"></i></span>
                <span>Tenancy Details</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Lessor (Owner)</span>
                    <span class="block font-bold text-slate-900 mt-1">{{ $contract->property->owner->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Lessee (Tenant)</span>
                    <span class="block font-bold text-slate-900 mt-1">{{ $contract->tenant->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Duration</span>
                    <span class="block text-slate-700 mt-1 font-semibold">
                        {{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Physical Allocation</span>
                    <span class="block text-slate-700 mt-1 font-semibold">
                        @if($contract->bed)
                            Room {{ $contract->room->room_number }} / Bed Space {{ $contract->bed->bed_number }}
                        @elseif($contract->room)
                            Room {{ $contract->room->room_number }}
                        @else
                            Entire Property
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Financial Obligations -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl"><i data-lucide="coins" class="w-4 h-4"></i></span>
                <span>Financial Dues Summary</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Rent Dues Rate</span>
                    <span class="block text-base font-extrabold text-slate-900 mt-1">TZS {{ number_format($contract->rent_amount) }} / {{ $contract->billing_frequency }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Refundable Security Deposit</span>
                    <span class="block text-base font-extrabold text-slate-900 mt-1">TZS {{ number_format($contract->deposit_amount) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Acceptance Sidebar -->
    <div class="lg:col-span-1">
        <div class="sticky top-20 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-950">Tenant Signature Status</h3>

            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-3 text-xs leading-relaxed text-slate-700">
                <p class="font-bold text-slate-900">Signatures Log</p>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Owner:</span>
                        <span class="font-semibold text-emerald-600">Generated & Approved</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tenant:</span>
                        <span class="font-semibold {{ $contract->tenant_accepted ? 'text-emerald-600' : 'text-amber-500' }}">
                            {{ $contract->tenant_accepted ? 'Signed' : 'Awaiting signature' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
